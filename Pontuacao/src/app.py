from fastapi import FastAPI, WebSocket, WebSocketDisconnect
import uvicorn

app = FastAPI()

clients = []

@app.websocket("/ws")
# Quando um ciente se conecta, essa função é chamada (ws://localhost:8000/ws)
async def websocket_endpoint(websocket: WebSocket):
    # Aceitamos a conexão WebSocket
    await websocket.accept()
    clients.append(websocket)

    print("Um cliente se conectou. Total de clientes conectados:", len(clients))

    # Mantemos a conexão aberta para receber mensagens do cliente
    try:
        while True:
            await websocket.receive_text()
    
    # Caso o cliente se desconecte, removemos ele da lista de clientes
    except WebSocketDisconnect:
        clients.remove(websocket)
        print("Um cliente se desconectou. Total de clientes conectados:", len(clients))

@app.post("/atualizar-dados")
async def atualizar():
    for client in clients:
        await client.send_text("dados_atualizados")

    return {
        "ok": True
    }

if __name__ == "__main__":
    uvicorn.run(app, host="0.0.0.0", port=8000)