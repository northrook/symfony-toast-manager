<?php

declare( strict_types = 1 );

namespace Northrook\Symfony;

use Countable;
use Stringable;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;

final readonly class ToastManager implements Countable
{

    public function __construct(
        private Http\RequestStack $requestStack,
    ) {}

    public function getNotifications( ?FlashBagInterface $flashBag = null ) : array {

        $flashBag ??= $this->flashBag();

        $flashes = array_merge( ... array_values( $flashBag->all() ) );

        $notifications = [];

        dump( $flashes );

        foreach ( $flashes as $value ) {
            dump( $value );
            // $level       = $value[ 'level' ];
            // $message     = $value[ 'message' ];
            // $description = $value[ 'description' ];
            // $timeout     = $value[ 'timeout' ];
            //
            // /** @var   Timestamp $timestamp */
            // $timestamp = $value[ 'timestamp' ];
            //
            // if ( isset( $notifications[ $message ] ) ) {
            //     $notifications[ $message ][ 'timestamp' ][ $timestamp->timestamp ] = $timestamp;
            // }
            //
            // else {
            //     $notifications[ $message ] = [
            //         'level'       => $level,
            //         'message'     => $message,
            //         'description' => $description,
            //         'timeout'     => $timeout,
            //         'timestamp'   => [ $timestamp->timestamp => $timestamp, ],
            //     ];
            // }
        }

        return $notifications;
        //
        // usort(
        //     $notifications, static fn ( $a, $b ) => ( end( $a[ 'timestamp' ] ) ) <=> ( end( $b[ 'timestamp' ] ) ),
        // );
        //
        // return $notifications;
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
     * @return $this
     */
    public function addToast(
        string  $type,
        string  $message,
        ?string $description = null,
        ?int    $timeoutMs = 4500,
    ) : ToastManager {

        foreach ( $this->readFlashBagContents() as $key => $value ) {
            dump( $key, $value );
        }

        $toast = new Toast( $type, $message, $description, $timeoutMs );

        $this->flashBag()->add( $type, $toast );

        return $this;
    }

    private function readFlashBagContents() : array {
        return $this->flashBag()->peekAll();
    }

    public function count() : int {
        return count( $this->readFlashBagContents() );
    }

}