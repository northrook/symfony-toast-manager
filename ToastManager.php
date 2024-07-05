<?php

declare( strict_types = 1 );

namespace Northrook\Symfony;

use Countable;
use Northrook\Symfony\ToastManager\Notification;
use Stringable;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use function Northrook\Core\Function\hashKey;

final readonly class ToastManager implements Countable
{

    public function __construct(
        private Http\RequestStack $requestStack,
    ) {}

    public function getNotifications( ?FlashBagInterface $flashBag = null ) : array {

        $flashBag ??= $this->flashBag();

        $flashes = $flashBag->all();

        $notifications = [];

        foreach ( $flashes as $type => $flash ) {
            foreach ( $flash as $message ) {
                if ( $message instanceof Notification ) {
                    $notifications[ $message->key ] = $message;
                }
                elseif ( is_string( $message ) | $message instanceof Stringable ) {
                    $key                   = hashKey( [ $type, $message ] );
                    $notifications[ $key ] = new Notification( $type, $message );
                }
            }
        }

        foreach ( $notifications as $type => $notification ) {
            dump( $notification->getTimestamp() );
        }

        usort(
            $notifications, static fn ( $a, $b ) => ( $b->timestamp ) <=> ( $a->timestamp ),
        );

        return $notifications;
    }


    /**
     * Retrieve the current {@see FlashBag} from the active {@see Session}.
     *
     * @return FlashBagInterface
     * @throws SessionNotFoundException if no session is active
     */
    private function flashBag() : FlashBagInterface {
        return $this->requestStack->getSession()->getFlashBag();
    }

    /**
     * Adds a simple flash message to the current session.
     *
     * @param string                   $type  = ['info', 'success', 'warning', 'error', 'notice'][$any]
     * @param string|Stringable|array  $message
     *
     * @return $this
     */
    public function addFlash( string $type, string | Stringable | array $message ) : ToastManager {
        $this->flashBag()->add( $type, $message );
        return $this;
    }

    /**
     * @param string       $type  = ['error', 'warning', 'info', 'success'][$any]
     * @param string       $message
     * @param null|string  $description
     * @param null|int     $timeoutMs
     *
     * @return Notification
     */
    public function addToast(
        string  $type,
        string  $message,
        ?string $description = null,
        ?int    $timeoutMs = 4500,
    ) : Notification {

        foreach ( $this->readFlashBagContents() as $key => $value ) {
            dump( $key, $value );
        }

        $toast = new Notification( $type, $message, $description, $timeoutMs );

        $this->flashBag()->add( $type, $toast );

        return $toast;
    }

    private function readFlashBagContents() : array {
        return $this->flashBag()->peekAll();
    }

    public function count() : int {
        return count( $this->readFlashBagContents() );
    }

}