import os
import re

def fix_blade_formatting(directory):
    # 1. Regex for broken PHP object operators: - > or -> with spaces
    # It avoids matching HTML comment closers --> by ensuring no preceding -
    broken_operator_pattern = r'(?<!-)(?:\s+-\s*>|-\s+>\s*|->\s+)'

    # 2. Regex for helper functions with string arguments that have unwanted whitespace/newlines
    # Targeted helpers: route, session, asset, url, config, trans, __
    # This matches the helper name, then whitespace/newlines inside the quotes.
    # Updated to capture ANY content inside quotes to fix internal spaces e.g. "products.get - models"
    helper_whitespace_pattern = r'(\b(?:route|session|asset|url|config|trans|__)\s*\(\s*)([\'"])(?P<key>[\s\S]*?)([\'"])'

    files_fixed = 0

    for root, dirs, files in os.walk(directory):
        for file in files:
            if file.endswith('.blade.php'):
                file_path = os.path.join(root, file)
                try:
                    with open(file_path, 'r', encoding='utf-8') as f:
                        content = f.read()

                    # Fix broken operators: e.g. "$obj -> prop" or "$obj - > prop" to "$obj->prop"
                    new_content = re.sub(broken_operator_pattern, '->', content)

                    # Fix helper whitespace: e.g. "route(' \n tasks.index ')" to "route('tasks.index')"
                    def helper_replacement(match):
                        prefix = match.group(1) # helper_name( + possible spaces
                        quote = match.group(2)  # ' or "
                        key = match.group('key').replace(' ', '').replace('\n', '').replace('\r', '').replace('\t', '') # the actual route/config name
                        closing_quote = match.group(4)
                        return f"{prefix}{quote}{key}{closing_quote}"

                    new_content = re.sub(helper_whitespace_pattern, helper_replacement, new_content)

                    if new_content != content:
                        with open(file_path, 'w', encoding='utf-8') as f:
                            f.write(new_content)
                        files_fixed += 1
                        print(f"Fixed formatting in: {file_path}")
                except Exception as e:
                    print(f"Error processing {file_path}: {e}")
    
    print(f"\nTotal files checked/fixed: {files_fixed}")

if __name__ == "__main__":
    # Target the resources/views directory
    base_dir = r'c:\wamp64\www\NewProject\resources\views'
    print(f"Scanning for broken PHP operators and helper whitespace in {base_dir}...")
    fix_blade_formatting(base_dir)
