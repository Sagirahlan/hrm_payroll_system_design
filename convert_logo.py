import base64

# Read the logo file
with open(r'c:\Users\Rowwww\Herd\hrm_payroll_system_design\public\images\WhatsApp Image 2026-01-22 at 10.28.01 AM.jpeg', 'rb') as image_file:
    encoded_string = base64.b64encode(image_file.read()).decode('utf-8')
    
# Write to output file
with open(r'c:\Users\Rowwww\Herd\hrm_payroll_system_design\kswb-logo-base64.txt', 'w') as output_file:
    output_file.write(encoded_string)
    
print("Base64 conversion complete!")
print(f"Logo size: {len(encoded_string)} characters")
