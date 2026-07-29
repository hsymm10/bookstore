import os
import glob

vue_files = glob.glob('/var/www/html/bookstore/frontend/**/*.vue', recursive=True)

for file_path in vue_files:
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()
    
    new_content = content
    # Remove iconify import
    new_content = new_content.replace("import { Icon } from '@iconify/vue'\n", "")
    new_content = new_content.replace("import { Icon } from '@iconify/vue'", "")
    
    # Replace emoji 📚
    if 'class="text-6xl">📚<' in new_content:
        new_content = new_content.replace('class="text-6xl">📚<', 'class="text-6xl"><Icon name="mdi:book-open-page-variant" class="text-6xl text-slate-300" /><')
    elif '📚' in new_content:
        new_content = new_content.replace('📚', '<Icon name="mdi:book-open-page-variant" class="text-xl" />')
        
    # Replace ShoppingCartIcon in BookCard.vue
    if 'BookCard.vue' in file_path:
        new_content = new_content.replace("import { ShoppingCartIcon } from '@heroicons/vue/24/outline'\n", "")
        new_content = new_content.replace("import { ShoppingCartIcon } from '@heroicons/vue/24/outline'", "")
        new_content = new_content.replace("<ShoppingCartIcon class=\"w-3.5 h-3.5\" />", '<Icon name="mdi:cart-outline" class="w-4 h-4" />')
        
    if new_content != content:
        with open(file_path, 'w', encoding='utf-8') as f:
            f.write(new_content)
        print(f"Fixed {file_path}")
