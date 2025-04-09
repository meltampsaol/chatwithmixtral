from transformers import AutoTokenizer, AutoModelForCausalLM
import torch
import os

# Set cache directory
os.environ['HF_HOME'] = 'g:/LaravelProjects2025/chatwithmixtral/model_cache'

def load_model():
    model_name = "mistralai/Mistral-7B-v0.1"
    cache_dir = os.environ['HF_HOME']
    
    # Check if the model is already cached
    if not os.path.exists(os.path.join(cache_dir, model_name.replace("/", os.sep))):
        print("Model not found in cache. Downloading...")
    
    # Load model and tokenizer from cache or download if not available
    tokenizer = AutoTokenizer.from_pretrained(model_name, cache_dir=cache_dir)
    model = AutoModelForCausalLM.from_pretrained(
        model_name,
        cache_dir=cache_dir,
        device_map="auto",
        torch_dtype=torch.float16,
        low_cpu_mem_usage=True
    )
    return model, tokenizer

model, tokenizer = load_model()

# Configure tokenizer
tokenizer.pad_token = tokenizer.eos_token
tokenizer.padding_side = "left"

def format_prompt(user_input, system_message=None):
    """Properly format prompts for Mistral"""
    if system_message:
        return f"""<s>[INST] <<SYS>>
{system_message}
<</SYS>>

{user_input} [/INST]"""
    return f"<s>[INST] {user_input} [/INST]"

def generate_response(prompt, system_message=None, **generation_kwargs):
    """Handle generation with proper parameter separation"""
    # Format the prompt with system message
    formatted_prompt = format_prompt(prompt, system_message)
    
    # Tokenize input
    inputs = tokenizer(
        formatted_prompt,
        return_tensors="pt",
        padding=True,
        return_attention_mask=True
    ).to(model.device)
    
    # Set default generation parameters
    default_params = {
        'max_new_tokens': 150,
        'temperature': 0.7,
        'do_sample': True,
        'pad_token_id': tokenizer.eos_token_id,
        'repetition_penalty': 1.1
    }
    default_params.update(generation_kwargs)
    
    # Generate response
    outputs = model.generate(
        input_ids=inputs.input_ids,
        attention_mask=inputs.attention_mask,
        **default_params
    )
    
    # Decode and clean response
    full_text = tokenizer.decode(outputs[0], skip_special_tokens=True)
    return full_text.split("[/INST]")[-1].strip()

# Example usage
if __name__ == "__main__":
    response = generate_response(
        prompt="Explain quantum entanglement to a 5 year old",
        system_message="You are a friendly physics professor",
        temperature=0.8  # This is a valid generation parameter
    )
    print("Response:", response)