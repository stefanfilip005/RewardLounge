<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use App\Models\Employee;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
        Event::listen(\Slides\Saml2\Events\SignedIn::class, function (\Slides\Saml2\Events\SignedIn $event) {
            //$messageId = $event->auth->getLastMessageId();
            $samlUser = $event->auth->getSaml2User();
            $attributes = $samlUser->getAttributes();
            if($employee = Employee::where('remoteId',$attributes['mnr'][0])->first()){
                return redirect('https://intern.rkhl.at?token='.$employee->createToken("API TOKEN")->plainTextToken);
            }
            exit;
        });
    }
}
