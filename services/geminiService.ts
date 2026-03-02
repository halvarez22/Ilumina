import { Product } from "../types";
import { CHATBOT_SYSTEM_INSTRUCTION } from "../constants";

export type GeminiHistoryItem = {
  role: "user" | "model";
  parts: Array<{ text: string }>;
};

type GeminiChatResponse = {
  text: string;
};

function buildSystemInstruction(products: Product[]): GeminiHistoryItem[] {
  const productCatalogString = products
    .map(
      (p) =>
        ` - SKU: ${p.id}, Nombre: ${p.name}, Descripción: ${p.description}, Precio: $${p.price.toFixed(
          2
        )}, Stock: ${p.stock}`
    )
    .join("\n");

  const fullInstruction = `${CHATBOT_SYSTEM_INSTRUCTION}
  
Aquí está el catálogo de productos actual para que puedas ayudar a los clientes. Úsalo como tu principal fuente de conocimiento. No inventes productos que no estén en esta lista.
  
CATÁLOGO:
${productCatalogString}
`;

  // Mantenemos un “historial” compatible con roles user/model.
  return [
    {
      role: "user",
      parts: [{ text: fullInstruction }],
    },
    {
      role: "model",
      parts: [{ text: "¡Entendido! Tengo el catálogo y estoy listo para ayudar." }],
    },
  ];
}

export const startChatWithCatalog = (products: Product[]): GeminiHistoryItem[] => {
  return buildSystemInstruction(products);
};

async function sleep(ms: number) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

export const sendMessage = async (
  history: GeminiHistoryItem[],
  newMessage: string,
  retries = 3
): Promise<{ text: string; groundingMetadata: undefined; history: GeminiHistoryItem[] }> => {
  try {
    const res = await fetch("/api/gemini_chat.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        history,
        message: newMessage,
      }),
    });

    const bodyText = await res.text();
    let data: GeminiChatResponse | null = null;
    try {
      data = JSON.parse(bodyText);
    } catch {
      data = null;
    }

    if (!res.ok) {
      // Mostrar el mensaje que devuelve el backend (message o details) para que el usuario vea el motivo real
      const msg =
        (data && typeof (data as any).message === "string" && (data as any).message) ||
        (data && typeof (data as any).details === "string" && (data as any).details) ||
        (data && typeof (data as any).text === "string" && (data as any).text) ||
        bodyText ||
        `Error del servidor (${res.status})`;
      const err = new Error(msg);
      (err as any).status = res.status;
      throw err;
    }

    const text = data?.text ?? "";
    const nextHistory: GeminiHistoryItem[] = [
      ...history,
      { role: "user", parts: [{ text: newMessage }] },
      { role: "model", parts: [{ text }] },
    ];

    return { text, groundingMetadata: undefined, history: nextHistory };
  } catch (error: any) {
    const status = error?.status;
    const shouldRetry =
      retries > 0 && (status === 503 || status === 429 || (typeof status !== "number" && `${error?.message}`.includes("503")));

    if (shouldRetry) {
      await sleep(2000);
      return sendMessage(history, newMessage, retries - 1);
    }
    throw error;
  }
};

