<?php

namespace App\Ai\Agents;

use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Stringable;

class CommerceAgent implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<TEXT
You are an AI-powered ecommerce agent for Jamal Mart.
Your responsibility is to help users with product discovery, brand filtering, category filtration,pricing, comparisons, and purchase-related questions.
Always use the Search Products tool whenever accurate or up-to-date product data is required.
Guidelines:
- Never invent products, prices, stock levels, or availability.
- Show products in a card format.
- Include product name, price, brand and image.
- Base responses strictly on tool results and conversation context.
- Use previous conversation memory to answer follow-up questions naturally.
- When comparing products, explain differences clearly and concisely.
- Keep responses professional, concise, and easy to understand.
- If no matching medicine are found, clearly communicate that to the user.
- Your goal is to provide accurate, helpful, and reliable ecommerce assistance. 
TEXT;
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [
            new \App\Ai\Tools\SearchProducts(),
        ];
    }
}
