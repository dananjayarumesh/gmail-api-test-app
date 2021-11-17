<?php

namespace App\Http\Controllers;


use Google\Client;
use Google\Service\Gmail;
use Google\Service\Gmail\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class HomeController extends Controller
{
    public function getClient()
    {
        $client = new Client();
        $client->setApplicationName("Gmail API");
        $client->setScopes(Gmail::GMAIL_SEND);
        $client->setRedirectUri('http://localhost:8000/google/callback');
        $client->setAuthConfig(config('services.google'));
        return $client;
    }


    public function send(Request $request)
    {
        $authUrl = $this->getClient()->createAuthUrl();
        return Redirect::to($authUrl);
    }

    public function callback(Request $request)
    {
        $client = $this->getClient();
        $client->setAccessToken(
            $client->fetchAccessTokenWithAuthCode($request->code)
        );

        $rawMessageString = "From: <rumesh@macroactive.com>\r\n";
        $rawMessageString .= "To: <rumeshpriv@gmail.com>\r\n";
        $rawMessageString .= 'Subject: =?utf-8?B?' . base64_encode('test email 2')
            . "?=\r\n";
        $rawMessageString .= "MIME-Version: 1.0\r\n";
        $rawMessageString .= "Content-Type: text/html; charset=utf-8\r\n";
        $rawMessageString .= 'Content-Transfer-Encoding: quoted-printable'
            . "\r\n\r\n";
        $rawMessageString .= "Content\r\n";
        $rawMessage = strtr(base64_encode($rawMessageString),
            array('+' => '-', '/' => '_'));

        $message = new Message();
        $message->setRaw($rawMessage);

        try {
            $response
                = (new Gmail($client))->users_messages->send('rumesh@macroactive.com',
                $message);
            return 'Message with ID: ' . $response->getId() . ' sent.';
        } catch (\Exception $e) {
            return 'An error occurred: ' . $e->getMessage();
        }
    }
}
