
import React from 'react';
import { ChatIcon, CloseIcon } from '../constants';

interface ChatbotButtonProps {
  onClick: () => void;
  isChatOpen: boolean;
}

const ChatbotButton: React.FC<ChatbotButtonProps> = ({ onClick, isChatOpen }) => {
  return (
    <button
      onClick={onClick}
      className="fixed bottom-6 right-6 bg-brand-gold text-brand-dark-primary p-4 rounded-full shadow-2xl hover:bg-yellow-400 focus:outline-none focus:ring-2 focus:ring-yellow-300 focus:ring-offset-2 focus:ring-offset-brand-dark-primary transition-all duration-200 ease-in-out z-50 transform hover:scale-110"
      aria-label={isChatOpen ? "Cerrar chat" : "Abrir chat de ayuda"}
    >
      {isChatOpen ? <CloseIcon className="w-7 h-7" /> : <ChatIcon className="w-7 h-7" />}
    </button>
  );
};

export default ChatbotButton;
