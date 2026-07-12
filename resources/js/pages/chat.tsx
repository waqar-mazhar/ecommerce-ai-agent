import { index } from "@/actions/App/Http/Controllers/ChatController";
import { cn } from "@/lib/utils";
import { usePage } from "@inertiajs/react";
import { useEffect, useRef, useState } from "react";
import ReactMarkdown from "react-markdown";
import remarkGfm from "remark-gfm";

const bounceBubbles = () => {
    return (
        <div className="flex gap-1">

            <span className="w-2 h-2 bg-slate-400 rounded-full animate-bounce" />
            <span className="w-2 h-2 bg-slate-400 rounded-full animate-bounce delay-100" />
            <span className="w-2 h-2 bg-slate-400 rounded-full animate-bounce delay-200" />

        </div>
    )
}

export default function Chat() {
    const { messages: messageList } = usePage().props;
    const [conversationId, setConversationId] = useState<string | null>('84976674');
    const [isStreaming, setIsStreaming] = useState(false);
    const [userPrompt, setUserPrompt] = useState('');
    const messagesEndRef = useRef<HTMLDivElement | null>(null);

    const [messages, setMessages] = useState([
        {
            role: "assistant",
            content:
                "Hello! I am your AI Agent. How can I help you today?",
        },

    ]);

    useEffect(() => {
        if (messageList) {
            setMessages(Object.values(messageList));
        } else {
            setMessages([
                {
                    role: "assistant",
                    content: "Hello! I am your AI Agent. How can I help you today?",
                },
            ]);
        }
    }, [messageList]);

    useEffect(() => {
        messagesEndRef.current?.scrollIntoView();
    }, [messages]);

    const sendMessage = async () => {
        if (!userPrompt.trim()) return;
        setIsStreaming(true);
        const message = userPrompt;

        setMessages((prev) => [
            ...prev,
            {
                role: "user",
                content: message,
            },
            {
                role: "assistant",
                content: "",
            },
        ]);

        setUserPrompt("");

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
        try {

            const response = await fetch("/chat/stream", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "text/event-stream",
                    ...(token
                        ? {
                            "X-CSRF-TOKEN": token,
                        }
                        : {}),
                },
                body: JSON.stringify({
                    message,
                    conversationId
                }),
            });


            if (!response.body) {
                return;
            }

            const reader = response.body.getReader();

            const decoder = new TextDecoder();


            while (true) {
                const { done, value } = await reader.read();

                if (done) break;

                const chunk = decoder.decode(value, {
                    stream: true,
                });


                const lines = chunk.split("\n");


                for (const line of lines) {

                    if (!line.startsWith("data:")) continue;


                    const data = line.replace("data:", "").trim();
                    console.log("DATA", data);
                    

                    if (data === "[DONE]") continue;


                    try {
                        const json = JSON.parse(data);

                        if (json.type === "conversation") {
                            setConversationId(json.conversationId);
                        }

                        if (json.type === "text_delta") {
                            setMessages((prev) => {
                                const updated = [...prev];
                                const lastIndex = updated.length - 1;

                                updated[lastIndex] = {
                                    ...updated[lastIndex],
                                    content: updated[lastIndex].content + json.delta,
                                };

                                return updated;
                            });
                        }
                    } catch (error) {
                        console.log("Parse error", error);
                    }
                }
            }

        } catch (error) {
            console.log(error);

        } finally {
            setIsStreaming(false);

        }
    };


    return (
        <div className="h-screen flex bg-slate-100 text-slate-900">
            {/* Main Chat */}
            <main className="flex-1 flex flex-col">
                {/* Header */}
                <header
                    className="h-20 bg-white border-b flex items-center justify-between px-6">

                    <div>
                        <h2 className="font-semibold">Ecommerce AI Agent</h2>
                        <p className="text-xs text-green-600">● Online</p>
                    </div>

                </header>

                {/* Messages */}
                <section
                    className="flex-1 overflow-y-auto p-6 space-y-5"
                >
                    {messages.map((msg, index) => (
                        <div
                            key={index}
                            className={`flex ${msg.role === "user"
                                ? "justify-end"
                                : "justify-start"
                                }`}
                        >
                            <div
                                className={cn('max-w-xl px-5 py-4 rounded-2xl text-sm leading-relaxed',
                                    msg.role === "user" ? "bg-blue-600 text-white rounded-br-md" : "bg-white border shadow-sm rounded-bl-md"
                                )}
                            >
                                {msg.role === "assistant" &&
                                    msg.content === "" &&
                                    isStreaming ? (
                                    bounceBubbles()
                                ) : msg.role === "assistant" ? (
                                    <ReactMarkdown remarkPlugins={[remarkGfm]}>
                                        {msg.content}
                                    </ReactMarkdown>
                                ) : (
                                    msg.content
                                )}
                                <div ref={messagesEndRef} />

                            </div>
                        </div>
                    ))}
                </section>



                {/* Input */}
                <footer
                    className="bg-white border-t p-4">
                    <div
                        className="flex gap-3 max-w-5xl mx-auto">
                        <input
                            value={userPrompt}
                            onChange={(e) => setUserPrompt(e.target.value)}
                            onKeyDown={(e) => {
                                if (e.key === "Enter")
                                    sendMessage();
                            }}
                            placeholder="Ask about a anything..."
                            className="flex-1 rounded-xl border px-5 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 "
                        />


                        <button
                            onClick={sendMessage}
                            className="bg-blue-600 text-white px-6 rounded-xl hover:bg-blue-700 transition">Send</button>

                    </div>

                </footer>


            </main>

        </div>
    );
}

Chat.layout = {
    breadcrumbs: [
        {
            title: 'AI Agent',
            href: index(),
        },
    ],
};
