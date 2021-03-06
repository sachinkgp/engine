<?php
/**
 * Upgrades Delegate
 */
namespace Minds\Core\Wire\Delegates;

use Minds\Core\Config;
use Minds\Core\Di\Di;
use Minds\Core\Wire\Wire;
use Minds\Core\Pro\Manager as ProManager;

class UpgradesDelegate
{
    /** @var Config */
    private $config;

    /** @var EntitiesBuilder */
    private $entitiesBuilder;

    /** @var ProManager */
    private $proManager;

    public function __construct($config = null, $entitiesBuilder = null, $proManager = null)
    {
        $this->config = $config ?: Di::_()->get('Config');
        $this->entitiesBuilder = $entitiesBuilder ?: Di::_()->get('EntitiesBuilder');
        $this->proManager = $proManager ?? Di::_()->get('Pro\Manager');
    }

    /**
     * On Wire
     * @param Wire $wire
     * @param string $receiver_address
     * @return Wire $wire
     */
    public function onWire($wire, $receiver_address): Wire
    {
        switch ($wire->getReceiver()->guid) {
            case $this->config->get('blockchain')['contracts']['wire']['plus_guid']:
                return $this->onPlusUpgrade($wire, $receiver_address);
                break;
            case $this->config->get('pro')['handler']:
                return $this->onProUpgrade($wire, $receiver_address);
                break;
        }
        return $wire; // Not expected
    }

    private function onPlusUpgrade($wire, $receiver_address): Wire
    {
        /*if (
            !(
                $receiver_address == 'offchain'
                || $receiver_address == $this->config->get('blockchain')['contracts']['wire']['plus_address']
            )
        ) {
            return $wire; //not offchain or potential onchain fraud
        }

        // 20 tokens
        if ($wire->getAmount() != "20000000000000000000") {
            return $wire; //incorrect wire amount sent
        }*/

        //set the plus period for this user
        $user = $wire->getSender();

        // rebuild the user as we can't trust upstream
        $user = $this->entitiesBuilder->single($user->getGuid(), [
            'cache' => false,
        ]);

        if (!$user) {
            return $wire;
        }

        $days = 30;
        $monthly = $this->config->get('upgrades')['plus']['monthly'];
        $yearly = $this->config->get('upgrades')['plus']['yearly'];
        
        switch ($wire->getMethod()) {
            case 'tokens':
                if ($monthly['tokens'] == $wire->getAmount() / (10 ** 18)) {
                    $days = 30;
                } elseif ($yearly['tokens'] == $wire->getAmount() / (10 ** 18)) {
                    $days = 365;
                } else {
                    return $wire;
                }
                break;
            case 'usd':
                if ($monthly['usd'] == $wire->getAmount() / 100) {
                    $days = 30;
                } elseif ($yearly['usd'] == $wire->getAmount() / 100) {
                    $days = 365;
                } else {
                    return $wire;
                }
                break;
            default:
                return $wire;
        }

        $expires = strtotime("+{$days} days", $wire->getTimestamp());

        $user->setPlusExpires($expires);
        $user->save();

        //$wire->setSender($user);
        return $wire;
    }

    private function onProUpgrade($wire, $receiver_address): Wire
    {
        //set the plus period for this user
        $user = $wire->getSender();

        // rebuild the user as we can't trust upstream
        $user = $this->entitiesBuilder->single($user->getGuid(), [
            'cache' => false,
        ]);

        if (!$user) {
            return $wire;
        }

        $days = 30;
        $monthly = $this->config->get('upgrades')['pro']['monthly'];
        $yearly = $this->config->get('upgrades')['pro']['yearly'];

        error_log($wire->getMethod());
        switch ($wire->getMethod()) {
            case 'tokens':
                error_log($wire->getAmount());
                  if ($monthly['tokens'] == $wire->getAmount() / (10 ** 18)) {
                      $days = 30;
                  } elseif ($yearly['tokens'] == $wire->getAmount() / (10 ** 18)) {
                      $days = 365;
                  } else {
                      return $wire;
                  }
                break;
            case 'usd':
                if ($monthly['usd'] == $wire->getAmount() / 100) {
                    $days = 30;
                } elseif ($yearly['usd'] == $wire->getAmount() / 100) {
                    $days = 365;
                } else {
                    return $wire;
                }
                break;
            default:
                return $wire;
        }

        $expires = strtotime("+{$days} days", $wire->getTimestamp());

        $this->proManager->setUser($user)
            ->enable($expires);

        return $wire;
    }
}
