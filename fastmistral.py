from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from typing import Optional
import uvicorn
from hugmistral_cache import generate_response, load_model

app = FastAPI(title="Mistral API")

# Load model once at startup
print("Loading model...")
model, tokenizer = load_model()
print("Model loaded successfully!")

class Query(BaseModel):
    prompt: str
    system_message: Optional[str] = None
    temperature: Optional[float] = 0.7
    max_tokens: Optional[int] = 150

@app.post("/generate")
async def generate(query: Query):
    try:
        response = generate_response(
            prompt=query.prompt,
            system_message=query.system_message,
            temperature=query.temperature,
            max_new_tokens=query.max_tokens
        )
        return {"response": response}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

if __name__ == "__main__":
    uvicorn.run(app, host="127.0.0.1", port=8080)