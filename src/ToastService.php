<?php

declare( strict_types = 1 );

namespace Northrook\Symfony\Service;

use Northrook\Symfony\Service\ToastService\Message;
use Stringable;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use function Northrook\hashKey;

final readonly class ToastService
{
    public function __construct( private Http\RequestStack $requestStack ) {
        dump( $this->flashBag() );
    }

    private function getFlashBagMessage( string $key ) : ?Message {
        return $this->flashBag()->get( $key )[ 0 ] ?? null;
    }

    /**
     * Adds a simple flash message to the current session.
     *
     * @param string                   $type  = ['info', 'success', 'warning', 'error', 'notice'][$any]
     * @param string|Stringable|array  $message
     *
     * @return $this
     */
    public function flash( string $type, string | Stringable | array $message ) : ToastService {
        $this->flashBag()->add( $type, $message );
        return $this;
    }

    public function message(
        string  $type,
        string  $title,
        ?string $description,
        ?int    $timeout = null,
    ) : Message {
        $flashKey = hashKey( [ $type, $title ] );

        /** @type ?Message $message } */

        if ( $this->flashBag()->has( $flashKey ) ) {
            $message = $this->getFlashBagMessage( $flashKey );
            $message?->bump( $description );
        }

        $message ??= new Message( $type, $title, $description, $timeout );

        $this->flashBag()->add( $flashKey, $message );

        return $message;
    }


    public function success( string $message, ?string $description = null ) : Message {
        return $this->message( 'success', $message, $description );
    }

    public function info( string $message, ?string $description = null ) : Message {
        return $this->message( 'info', $message, $description );
    }

    public function notice( string $message, ?string $description = null ) : Message {
        return $this->message( 'notice', $message, $description );
    }

    public function warning( string $message, ?string $description = null ) : Message {
        return $this->message( 'warning', $message, $description );
    }

    public function danger( string $message, ?string $description = null ) : Message {
        return $this->message( 'danger', $message, $description );
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

    private function flashBagContents() : array {
        return $this->flashBag()->peekAll();
    }

}