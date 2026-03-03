import sys
import os

files = [
    r"c:\xampp\htdocs\sokosafi\assets\css\theme-light.css",
    r"c:\xampp\htdocs\sokosafi\temp_theme_light.css",
    r"c:\xampp\htdocs\sokosafi\pages\home.php",
    r"c:\xampp\htdocs\sokosafi\pages\new_arrivals.php",
    r"c:\xampp\htdocs\sokosafi\pages\featured.php",
    r"c:\xampp\htdocs\sokosafi\pages\checkout.php"
]

for f in files:
    try:
        with open(f, 'r', encoding='utf-8') as file:
            c = file.read()
        
        c = c.replace('object-fit: cover', 'object-fit: contain')
        
        # Restore categories to cover
        c = c.replace('.category-card img {\n    width: 100%;\n    height: 100%;\n    object-fit: contain;', '.category-card img {\n    width: 100%;\n    height: 100%;\n    object-fit: cover;')
        c = c.replace('.category-card img{width:100%;height:100%;object-fit:contain;transition:transform 0.5s ease}', '.category-card img{width:100%;height:100%;object-fit:cover;transition:transform 0.5s ease}')
        
        with open(f, 'w', encoding='utf-8') as file:
            file.write(c)
        print(f"Updated {f}")
    except Exception as e:
        print(f"Error on {f}: {e}")
