<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;

class ChatbotController extends Controller
{
    public function index()
    {
        return view('chatbot');
    }

    public function chat(Request $request)
    {
        $messages = $request->input('messages');

        $response = OpenAI::chat()->create([
            'model' => 'gpt-3.5-turbo', // You can change this to another model like 'gpt-4'
            'messages' => $messages,
        ]);

        return response()->json([
            'message' => $response->choices[0]->message->content,
        ]);
    }
}
