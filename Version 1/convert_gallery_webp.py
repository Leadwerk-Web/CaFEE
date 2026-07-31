import os
from PIL import Image

def convert_to_webp(directory, quality=85):
    for filename in os.listdir(directory):
        if filename.endswith(".jpg"):
            filepath = os.path.join(directory, filename)
            webp_filepath = os.path.join(directory, os.path.splitext(filename)[0] + ".webp")
            try:
                with Image.open(filepath) as img:
                    img.save(webp_filepath, 'webp', quality=quality)
                print(f"Converted {filename} to WebP.")
            except Exception as e:
                print(f"Error converting {filename}: {e}")

convert_to_webp("Bildergalerie", 85)
