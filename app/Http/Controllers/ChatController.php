<?php

namespace App\Http\Controllers;

use App\Ai\Agents\CommerceAgent;
use App\Ai\Agents\ProductAgent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Laravel\Ai\Responses\StreamedAgentResponse;
use Laravel\Ai\Messages\Message;

class ChatController extends Controller
{
    public function index()
    {
        $messages = DB::table('agent_conversation_messages')->where('user_id', Auth::user()->id)
            ->latest('id')
            ->limit(50)
            ->get()
            ->reverse()
            ->values()
            ->map(function ($message) {
                return new Message($message->role, $message->content);
            })->all();
        return Inertia::render('chat', [
            'messages' => $messages
        ]);
    }

    public function stream(Request $request)
    {
        $request->validate([
            'message' => ['required', 'string']
        ]);

        $user = Auth::user();
        $conversationId = $request->conversationId ? $request->conversationId : $this->getCurrentConversation($user);
        $stream = $conversationId ? (new CommerceAgent)->continue($conversationId, as: $user)->stream($request->message) : (new CommerceAgent)->forUser($user)->stream($request->message);

        return $stream->then(function (StreamedAgentResponse $response) {
            Log::info($response);
            return $response;
        });
    }

    public function getCurrentConversation($user)
    {
        $id = rand(11111111, 99999999);

        DB::table('agent_conversations')->insert([
            'id' => $id,
            'user_id' => $user->id,
            'title' => 'Start',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }
}
