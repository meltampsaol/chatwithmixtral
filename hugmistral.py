from transformers import AutoTokenizer, AutoModelForCausalLM
import torch
import time

# Load model and tokenizer with optimized settings
model_name = "mistralai/Mistral-7B-v0.1"
tokenizer = AutoTokenizer.from_pretrained(model_name)
model = AutoModelForCausalLM.from_pretrained(
    model_name,
    device_map="auto",
    torch_dtype=torch.float16,
    low_cpu_mem_usage=True
)

# Configure tokenizer for Mistral's instruction format
tokenizer.pad_token = tokenizer.eos_token
tokenizer.padding_side = "left"

def format_prompt(user_input, system_message=None):
    """Format prompts using Mistral's recommended instruction template"""
    if system_message:
        return f"""<s>[INST] <<SYS>>
{system_message}
<</SYS>>

{user_input} [/INST]"""
    return f"<s>[INST] {user_input} [/INST]"

def generate_batch_responses(prompts, system_message=None, **generation_args):
    """Process multiple inputs with proper batching and formatting"""
    # Format prompts with instruction template
    formatted_prompts = [format_prompt(prompt, system_message) for prompt in prompts]
    
    # Tokenize with batch processing
    inputs = tokenizer(
        formatted_prompts,
        return_tensors="pt",
        padding=True,
        truncation=True,
        max_length=2048,
        return_attention_mask=True
    ).to(model.device)
    
    # Set default generation parameters
    default_args = {
        'max_new_tokens': 256,
        'temperature': 0.7,
        'top_p': 0.9,
        'do_sample': True,
        'pad_token_id': tokenizer.eos_token_id,
        'repetition_penalty': 1.1
    }
    default_args.update(generation_args)
    
    # Generate responses
    start_time = time.time()
    outputs = model.generate(
        input_ids=inputs.input_ids,
        attention_mask=inputs.attention_mask,
        **default_args
    )
    
    # Decode and post-process
    responses = []
    for i, output in enumerate(outputs):
        full_text = tokenizer.decode(output, skip_special_tokens=True)
        # Extract only the response part after [/INST]
        response = full_text.split("[/INST]")[-1].strip()
        responses.append(response)
    
    print(f"Generated {len(prompts)} responses in {time.time()-start_time:.2f}s")
    return responses

# Example usage
if __name__ == "__main__":
    # Single prompt example
    system_msg = "You are a helpful assistant with a witty sense of humor."
    single_prompt = "Explain quantum physics using a pirate analogy"
    
    single_response = generate_batch_responses([single_prompt], system_msg)[0]
    print("\nSingle Response Example:")
    print(single_response)
    
    # Batch processing example
    batch_prompts = [
        "Give me a recipe for vegan chocolate cake",
        "Write a haiku about artificial intelligence",
        "What's the Fermi Paradox? Explain simply"
    ]
    
    print("\nBatch Processing Results:")
    batch_responses = generate_batch_responses(
        batch_prompts,
        system_message="You are an expert AI that provides clear, concise answers",
        temperature=0.8,
        max_new_tokens=150
    )
    
    for i, (prompt, response) in enumerate(zip(batch_prompts, batch_responses)):
        print(f"\nPrompt {i+1}: {prompt}")
        print(f"Response: {response}\n")
