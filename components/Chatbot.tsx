import React, { useState, useEffect, useRef } from 'react';
import { sendMessage, startChatWithCatalog, GeminiHistoryItem } from '../services/geminiService';
import { getProducts } from '../services/productService';
import { ChatMessage, GroundingMetadata } from '../types';
import { BotIcon, UserIcon, SendIcon } from '../constants';
import Modal from './Modal';

interface ChatbotProps {
  isOpen: boolean;
  onClose: () => void;
}

const Chatbot: React.FC<ChatbotProps> = ({ isOpen, onClose }) => {
  const [messages, setMessages] = useState<ChatMessage[]>([]);
  const [inputValue, setInputValue] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const messagesEndRef = useRef<HTMLDivElement>(null);
  const [chatHistory, setChatHistory] = useState<GeminiHistoryItem[] | null>(null);

  useEffect(() => {
    if (isOpen) {
      // Cargar productos y Iniciar la sesion de chat cuando se abre el chat
      getProducts()
        .then(products => {
          setChatHistory(startChatWithCatalog(products));
        })
        .catch((err: Error) => {
          console.error("Error cargando productos para el chatbot:", err);
          setError("No pude cargar el catálogo de productos.");
        });

      if (messages.length === 0) {
        setMessages([
          {
            id: 'initial-bot-message',
            text: `¡Hola! Soy Lumi, tu asistente virtual de Ilumileds. ¿Cómo puedo ayudarte a encontrar la iluminación perfecta hoy?`,
            sender: 'bot',
            timestamp: new Date(),
          },
        ]);
      }
    }
  }, [isOpen]);

  useEffect(() => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  }, [messages]);

  const handleSendMessage = async () => {
    const trimmedInput = inputValue.trim();
    if (!trimmedInput || isLoading || !chatHistory) return;

    const userMessage: ChatMessage = {
      id: `user-${Date.now()}`,
      text: trimmedInput,
      sender: 'user',
      timestamp: new Date(),
    };
    setMessages((prev: ChatMessage[]) => [...prev, userMessage]);
    setInputValue('');
    setIsLoading(true);
    setError(null);

    try {
      const response = await sendMessage(chatHistory, trimmedInput);
      setChatHistory(response.history);
      
      const botMessage: ChatMessage = {
        id: `bot-${Date.now()}`,
        text: response.text,
        sender: 'bot',
        timestamp: new Date(),
        // @ts-ignore
        groundingMetadata: response.groundingMetadata
      };
      setMessages((prev: ChatMessage[]) => [...prev, botMessage]);
    } catch (err: any) {
      const errorMessage = `${err?.message || 'Lo siento, ocurrió un error inesperado.'}`;
      setError(errorMessage);
      const botMessage: ChatMessage = {
        id: `bot-error-${Date.now()}`,
        text: `Lo siento, no pude procesar tu solicitud. Error: ${errorMessage}`,
        sender: 'bot',
        timestamp: new Date(),
      };
      setMessages((prev: ChatMessage[]) => [...prev, botMessage]);
    } finally {
      setIsLoading(false);
    }
  };

  const handleKeyPress = (event: React.KeyboardEvent<HTMLInputElement>) => {
    if (event.key === 'Enter') {
      handleSendMessage();
    }
  };
  
  const renderGrounding = (metadata?: GroundingMetadata) => {
    // @ts-ignore
    if (!metadata || !metadata.groundingChunks || metadata.groundingChunks.length === 0) {
      return null;
    }
    // @ts-ignore
    const webChunks = metadata.groundingChunks.filter(chunk => chunk.web && chunk.web.uri && chunk.web.title);
    if (webChunks.length === 0) return null;

    return (
      <div className="mt-2 text-xs text-brand-text-secondary">
        <p className="font-semibold mb-1">Fuentes consultadas:</p>
        <ul className="list-disc list-inside pl-2 space-y-1">
          {webChunks.map((chunk, index) => (
            <li key={index}>
              <a 
                href={chunk.web!.uri} 
                target="_blank" 
                rel="noopener noreferrer" 
                className="text-brand-gold hover:underline"
              >
                {chunk.web!.title || chunk.web!.uri}
              </a>
            </li>
          ))}
        </ul>
      </div>
    );
  };

  return (
    <Modal isOpen={isOpen} onClose={onClose} title="Asistente Virtual Ilumileds" size="lg">
      <div className="flex flex-col h-[70vh] sm:h-[60vh]">
        <div className="flex-grow space-y-4 p-1 overflow-y-auto mb-4 scrollbar-thin scrollbar-thumb-gray-600 scrollbar-track-brand-dark-secondary">
          {messages.map((msg: ChatMessage) => (
            <div
              key={msg.id}
              className={`flex items-end space-x-2 ${
                msg.sender === 'user' ? 'justify-end' : 'justify-start'
              }`}
            >
              {msg.sender === 'bot' && <BotIcon className="w-6 h-6 text-brand-gold self-start flex-shrink-0" />}
              <div
                className={`max-w-[70%] p-3 rounded-xl shadow ${
                  msg.sender === 'user'
                    ? 'bg-brand-gold text-brand-dark-primary rounded-br-none'
                    : 'bg-brand-dark-secondary text-brand-text-primary rounded-bl-none border border-gray-700'
                }`}
              >
                <p className="text-sm whitespace-pre-wrap">
                  {msg.text}
                </p>
                {msg.sender === 'bot' && renderGrounding(msg.groundingMetadata)}
              </div>
              {msg.sender === 'user' && <UserIcon className="w-6 h-6 text-brand-silver self-start flex-shrink-0" />}
            </div>
          ))}
          <div ref={messagesEndRef} />
        </div>
        {error && <p className="text-red-400 text-sm mb-2 text-center">{error}</p>}
        <div className="flex-shrink-0 flex items-center p-1 border-t border-gray-700">
          <input
            type="text"
            value={inputValue}
            onChange={(e) => setInputValue(e.target.value)}
            onKeyPress={handleKeyPress}
            placeholder="Escribe tu consulta aquí..."
            className="w-full bg-brand-dark-secondary text-brand-text-primary placeholder-brand-text-secondary border-none rounded-md px-4 py-2 focus:ring-2 focus:ring-brand-gold focus:outline-none"
            disabled={isLoading}
          />
          <button onClick={handleSendMessage} disabled={isLoading || !inputValue} className="ml-2 p-2 bg-brand-gold text-brand-dark-primary rounded-md hover:bg-yellow-400 disabled:bg-gray-600 disabled:cursor-not-allowed">
            {isLoading ? (
              <div className="w-5 h-5 border-2 border-t-transparent rounded-full animate-spin"></div>
            ) : (
              <SendIcon className="w-5 h-5" />
            )}
          </button>
        </div>
      </div>
    </Modal>
  );
};

export default Chatbot;
