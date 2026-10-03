<label for="{{ $name }}">{{ $label }}</label>
<input type="{{ $type ?? "text" }}" name="{{ $name }}" value="{{ old($name) }}" class="w-full mt-1 p-2 border rounded @error($name) border-red-500 @enderror"> 
@error($name)
    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
@enderror