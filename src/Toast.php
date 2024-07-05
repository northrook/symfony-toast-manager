<?php

namespace Northrook\Symfony;

use Countable, InvalidArgumentException;
use Northrook\Core\Timestamp;
use Northrook\Core\Trait\PropertyAccessor;
use Northrook\Logger\Log;
use function Northrook\Core\Function\hashKey;

/**
 * @property-read string     $key
 * @property-read  string    $type  ['info', 'success', 'warning', 'error', 'notice'][$any]
 * @property-read  string    $message
 * @property-read  ?string   $description
 * @property-read  ?int      $timeout
 * @property-read  array     $instances
 * @property-read  Timestamp $timestamp
 */
final class Toast implements Countable
{
    use PropertyAccessor;

    private array   $instances  = [];
    private string  $type;
    private string  $message;
    private ?string $description;
    private array   $parameters = [];

    /**
     * How long before the message should time out and disappear?
     *
     * - `null` - use defaults if available, else `0`
     * - `0` - never times out
     * - `#` - timeout in milliseconds | recommended 4500
     *
     * @var ?int
     */
    private ?int $timeout;

    /**
     * @param string       $type  ['info', 'success', 'warning', 'error', 'notice'][$any]
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
        $this->setType( $type );
        $this->message     = trim( $message );
        $this->description = $description ? trim( $description ) : null;
        $this->timeout     = $timeout;

        $this->instances[ 'initial' ] = new Timestamp();
    }

    public function bump() : Toast {
        $this->instances[] = new Timestamp();
        return $this;
    }

    private function setType( string $type ) : void {
        try {
            $this->type = in_array( $type, [ 'success', 'info', 'warning', 'danger' ] )
                ? $type
                : throw new InvalidArgumentException( "Invalid type '{$type}' used for " . Toast::class );
        }
        catch ( InvalidArgumentException $exception ) {
            Log::exception( $exception );
            $this->type = 'notice';
        }
    }

    public function __get( string $property ) {
        return match ( $property ) {
            'key'         => hashKey( [ $this->type, $this->message, $this->description, $this->timeout ] ),
            'type'        => $this->type,
            'message'     => $this->message,
            'description' => $this->description,
            'timeout'     => $this->timeout,
            'instances'   => $this->instances,
            'timestamp'   => $this->instances[ 'initial' ],
        };
    }

    public function notice() : Toast {
        $this->type = 'notice';
        return $this;
    }

    public function info() : Toast {
        $this->type = 'info';
        return $this;
    }

    public function success() : Toast {
        $this->type = 'success';
        return $this;
    }

    public function warning() : Toast {
        $this->type = 'warning';
        return $this;
    }

    public function error() : Toast {
        $this->type = 'error';
        return $this;
    }

    public function timeout( ?int $timeout ) : Toast {
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