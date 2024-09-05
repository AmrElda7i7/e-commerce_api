<?php 
namespace App\Exceptions;

use App\Traits\ApiHandlerTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class InsufficientStockException extends Exception
{
    use ApiHandlerTrait ;
    /**
     * Create a new exception instance.
     *
     * @param string|null $message
     * @return void
     */
    public function __construct(string $message = 'Insufficient stock available')
    {
        parent::__construct($message);
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  Request  $request
     * @return Response
     */
    public function render(Request $request): Response
    {
      
        $status = 400;
        $error = "Insufficient stock";
        $message = $this->getMessage();

        return response(["error" => $error, "message" => $message], $status);
    }
}
