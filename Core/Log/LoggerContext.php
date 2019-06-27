<?php
/**
 * LoggerContext
 * @author edgebal
 */

namespace Minds\Core\Log;

use Exception;
use Minds\Core\Config;
use Minds\Core\Di\Di;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ChromePHPHandler;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\PHPConsoleHandler;
use Monolog\Logger as MonologLogger;
use Throwable;

class LoggerContext
{
    /** @var Config */
    protected $config;

    /** @var string */
    protected $context;

    /** @var ErrorLogHandler */
    protected $errorLogHandler;

    /** @var MonologLogger */
    protected $logger;

    /**
     * LoggerContext constructor.
     * @param Config $config
     */
    public function __construct($config = null)
    {
        $this->config = $config ?: Di::_()->get('Config');

        $this->setUp();
        $this->setContext('Default');
    }

    /**
     * @param string $context
     * @return LoggerContext
     */
    public function setContext(string $context)
    {
        $this->context = $context;

        // NOTE: Monolog context (array of extra data) is not the same as our context (calling class)
        $format = "%datetime% %channel%.%level_name%: [{$this->context}] %message% %context% %extra%\n";
        $dateFormat = 'c';
        $lineFormatter = new LineFormatter($format, $dateFormat, false, true);

        if ($this->errorLogHandler) {
            $this->errorLogHandler->setFormatter($lineFormatter);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function setUp()
    {
        $isProduction = !$this->config->get('development_mode');
        $level = $this->config->get('min_log_level') ?: ($isProduction ? MonologLogger::INFO : MonologLogger::DEBUG);

        $this->logger = new MonologLogger('minds');

        if ($isProduction) {
            // Production handlers
            $this->errorLogHandler = new ErrorLogHandler(
                ErrorLogHandler::OPERATING_SYSTEM,
                $level
            );

            $this->logger->pushHandler($this->errorLogHandler);
        } else {
            // Development handlers

            $this->errorLogHandler = new ErrorLogHandler();
            $this->logger->pushHandler($this->errorLogHandler);

            switch ($this->config->get('devtools_logger') ?: '') {
                case 'firephp':
                    $this->logger->pushHandler(new FirePHPHandler());
                    break;

                case 'chromelogger':
                    $this->logger->pushHandler(new ChromePHPHandler());
                    break;

                case 'phpconsole':
                    try {
                        $this->logger->pushHandler(new PHPConsoleHandler($this->config->get('devtools_logger_opts') ?: []));
                    } catch (Exception $exception) {
                        // If the server-side vendor package is not installed, ignore any warnings.
                    }

                    break;
            }
        }

        return true;
    }

    /**
     * @param string|Throwable $message
     * @param array $payload
     * @return bool
     */
    public function emergency($message, array $payload = [])
    {
        return $this->logger->emergency(...$this->buildMonologCall($message, $payload));
    }

    /**
     * @param string|Throwable $message
     * @param array $payload
     * @return bool
     */
    public function alert($message, array $payload = [])
    {
        return $this->logger->alert(...$this->buildMonologCall($message, $payload));
    }

    /**
     * @param string|Throwable $message
     * @param array $payload
     * @return bool
     */
    public function critical($message, array $payload = [])
    {
        return $this->logger->critical(...$this->buildMonologCall($message, $payload));
    }

    /**
     * @param string|Throwable $message
     * @param array $payload
     * @return bool
     */
    public function error($message, array $payload = [])
    {
        return $this->logger->error(...$this->buildMonologCall($message, $payload));
    }

    /**
     * @param string|Throwable $message
     * @param array $payload
     * @return bool
     */
    public function warning($message, array $payload = [])
    {
        return $this->logger->warning(...$this->buildMonologCall($message, $payload));
    }

    /**
     * @param string|Throwable $message
     * @param array $payload
     * @return bool
     */
    public function notice($message, array $payload = [])
    {
        return $this->logger->notice(...$this->buildMonologCall($message, $payload));
    }

    /**
     * @param string|Throwable $message
     * @param array $payload
     * @return bool
     */
    public function info($message, array $payload = [])
    {
        return $this->logger->info(...$this->buildMonologCall($message, $payload));
    }

    /**
     * @param string|Throwable $message
     * @param array $payload
     * @return bool
     */
    public function debug($message, array $payload = [])
    {
        return $this->logger->debug(...$this->buildMonologCall($message, $payload));
    }

    /**
     * @param string|Throwable $message
     * @param array $payload
     * @return array
     */
    protected function buildMonologCall($message, array $payload = [])
    {
        $monologMessage = $message;
        $monologContext = array_merge(['_log' => $this->context], $payload ?: []);

        if ($message instanceof Throwable) {
            $throwableClass = trim(get_class($message), '\\');
            $monologMessage = "[{$throwableClass}] {$message->getMessage()}";
            $monologContext['_trace'] = $message->getTraceAsString();
        }

        return [$monologMessage, $monologContext];
    }
}
