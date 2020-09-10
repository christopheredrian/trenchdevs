<?php

namespace App\Exceptions;

use App\Models\EmailQueue;
use ErrorException;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param Throwable $exception
     * @return void
     *
     * @throws Exception|Throwable
     */
    public function report(Throwable $exception)
    {

        if (env('APP_ENV') !== 'local') {
            if ($exception instanceof ErrorException) {
                $this->sendEmailToSupport($exception);
                abort(500, "There was an error while processing your request. Admin has been notified");
            }
        }

        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param Request $request
     * @param Throwable $exception
     * @return Response
     *
     * @throws Throwable
     */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof TrenchDevsWebApiException) {
            return \response()->json([
                'errors' => $exception->getUniqueErrors(),
                'message' => $exception->getMessage(),
                'status' => 'error',
            ]);
        }

        return parent::render($request, $exception);
    }

    /**
     * @param Throwable $throwable
     */
    private function sendEmailToSupport(Throwable $throwable)
    {

        try {

            if (env('APP_ENV') !== 'production') {
                throw new Exception("Suppress error");
            }

            $title = "TrenchDevs: Error Reporting";

            $emailer = EmailQueue::queue(
                trim('support@trenchdevs.org'),
                $title,
                $viewData = [
                    'name' => null,
                    'email_body' => "<pre>" .$throwable->getMessage() . "\n" . $throwable->getTraceAsString() . "</pre>",
                ],
                'emails.generic'
            );

            $emailer->send();

        } catch (Throwable $throwable) {
            // this time just suppress.
        }
    }
}
