from transformers import AutoTokenizer, AutoModelForCausalLM

# Model name on Hugging Face
MODEL_NAME = "mistralai/Mistral-7B-v0.1"  # Replace with the actual Hugging Face path if updated

# Load tokenizer and model
print("Downloading model...")
tokenizer = AutoTokenizer.from_pretrained(MODEL_NAME)
model = AutoModelForCausalLM.from_pretrained(MODEL_NAME)

print("Model downloaded and loaded successfully!")
