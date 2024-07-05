<?php

namespace Northrook\Symfony\ToastManager;

use Countable;
use InvalidArgumentException;
use Northrook\Core\Timestamp;
use Northrook\Core\Trait\PropertyAccessor;
use Northrook\Logger\Log;
use function array_key_last;
use function in_array;
use function Northrook\Core\Function\hashKey;
use function trim;

/**
 *
 * @property-read string     $key         // Unique key to identify this object internally
 * @property-read  string    $type        // One of 'info', 'success', 'warning', 'error', or 'notice'
 * @property-read  string    $message     // The main message to show the user
 * @property-read  ?string   $description // [optional] Provide more details.
 * @property-read  ?int      $timeout     // How long before the message should time out, in milliseconds
 * @property-read  array     $instances   // All the times this exact Notification has been created since it was last rendered
 * @property-read  Timestamp $timestamp   // The most recent timestamp
 *
 * @internal
 * @author Martin Nielsen <mn@northrook.com>
 */
final class Notification implements Countable
{

    use PropertyAccessor;

    private array   $instances = [];
    private string  $type;
    private string  $message;
    private ?string $description;
    private ?int    $timeout;

    /**
     * @param string       $type  = [ 'info', 'success', 'warning', 'error', 'notice' ][$any]
     * @param string       $message
     * @param null|string  $description
     * @param null|int     $timeout
     */
    public function __construct(
        string  $type,
        string  $message,
        ?string $description = null,
        ?int    $timeout = null,
    ) {

        // If the $type is a valid level, add it, otherwise throw an exception for incident management
        try {
            $this->type = in_array( $type, [ 'info', 'success', 'warning', 'error', 'notice' ] )
                ? $type
                : throw new InvalidArgumentException( "Invalid type '{$type}' used for " . Notification::class );
        }
            // Immediately catch and log the exception, then set the type to 'notice'
        catch ( InvalidArgumentException $exception ) {
            Log::exception( $exception );
            $this->type = 'notice';
        }

        $this->message     = trim( $message );
        $this->description = $description ? trim( $description ) : null;
        $this->timeout     = $timeout;
        $this->instances[] = new Timestamp();
    }

    public function __get( string $property ) : null | string | int | array {
        return match ( $property ) {
            'key'         => hashKey( [ $this->type, $this->message, $this->description, $this->timeout ] ),
            'type'        => $this->type,
            'message'     => $this->message,
            'description' => $this->description,
            'timeout'     => $this->timeout,
            'instances'   => $this->instances,
            'timestamp'   => $this->instances[ array_key_last( $this->instances ) ],
        };
    }

    /**
     * Indicate that this notification has been seen before.
     *
     * - Adds a timestamp to the {@see Notification::$instances} array.
     *
     * @return $this
     */
    public function bump() : Notification {
        $this->instances[] = new Timestamp();
        return $this;
    }

    public function notice() : Notification {
        $this->type = 'notice';
        return $this;
    }

    public function info() : Notification {
        $this->type = 'info';
        return $this;
    }

    public function success() : Notification {
        $this->type = 'success';
        return $this;
    }

    public function warning() : Notification {
        $this->type = 'warning';
        return $this;
    }

    public function error() : Notification {
        $this->type = 'error';
        return $this;
    }

    public function timeout( ?int $timeout ) : Notification {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * How many times has this been triggered since the last render?
     *
     * @return int
     */
    public function count() : int {
        return count( $this->instances );
    }

}