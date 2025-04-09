from transformers import AutoTokenizer, AutoModelForCausalLM
import torch

# Load the model and tokenizer
model_name = "mistralai/Mistral-7B"  # Hugging Face model name for Mistral-7B
tokenizer = AutoTokenizer.from_pretrained(model_name)
model = AutoModelForCausalLM.from_pretrained(model_name)

# Set the model to run on CPU
device = torch.device("cpu")  # Change this to "cuda" for GPU if available
model.to(device)

# Function to generate response
def generate_response(prompt):
    inputs = tokenizer(prompt, return_tensors="pt")
    inputs = {key: value.to(device) for key, value in inputs.items()}  # Move input to the same device as the model
    outputs = model.generate(inputs['input_ids'], max_length=50)
    response = tokenizer.decode(outputs[0], skip_special_tokens=True)
    return response

# Test the model
user_prompt = "Hello, tell me a joke."
response = generate_response(user_prompt)
print("Model Response:", response)
