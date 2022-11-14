<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Extensions\WebauthnCredentialBroker;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Facades\Password;
use App\Http\Requests\WebauthnDeviceLostRequest;

class WebAuthnDeviceLostController extends Controller
{
    use ResetsPasswords;


    /**
     * Send a recovery email to the user.
     *
     * @param \App\Http\Requests\WebauthnDeviceLostRequest  $request
     * @param  \App\Extensions\WebauthnCredentialBroker  $broker
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function sendRecoveryEmail(WebauthnDeviceLostRequest $request, WebauthnCredentialBroker $broker)
    {
        $credentials = $request->validated();

        $response = $broker->sendResetLink($credentials);

        return $response === Password::RESET_LINK_SENT
            ? $this->sendRecoveryLinkResponse($request, $response)
            : $this->sendRecoveryLinkFailedResponse($request, $response);
    }


    /**
     * Get the response for a failed account recovery link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendRecoveryLinkFailedResponse(Request $request, string $response)
    {
        if ($request->wantsJson()) {
            throw ValidationException::withMessages(['email' => [trans($response)]]);
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => trans($response)]);
    }


    /**
     * Get the response for a successful account recovery link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendRecoveryLinkResponse(Request $request, string $response)
    {
        return response()->json(['message' => __('auth.webauthn.account_recovery_email_sent')]);
    }
}