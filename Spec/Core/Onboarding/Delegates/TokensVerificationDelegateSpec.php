<?php

namespace Spec\Minds\Core\Onboarding\Delegates;

use Minds\Core\Onboarding\Delegates\TokensVerificationDelegate;
use Minds\Entities\User;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class TokensVerificationDelegateSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(TokensVerificationDelegate::class);
    }

    public function it_should_check_if_completed(User $user)
    {
        $user->getPhoneNumberHash()
            ->shouldBeCalled()
            ->willReturn('0303456');

        $this
            ->isCompleted($user)
            ->shouldReturn(true);
    }

    public function it_should_check_if_not_completed(User $user)
    {
        $user->getPhoneNumberHash()
            ->shouldBeCalled()
            ->willReturn(null);

        $this
            ->isCompleted($user)
            ->shouldReturn(false);
    }
}
