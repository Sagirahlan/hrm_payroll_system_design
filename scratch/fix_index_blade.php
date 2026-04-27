<?php
$file = 'resources/views/pensioners/index.blade.php';
$content = file_get_contents($file);

// 1. Update the button
$oldButtonPart = '<li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <button type="button" class="dropdown-item text-danger delete-pensioner-btn" 
                                                            data-id="{{ $pensioner->id }}" 
                                                            data-name="{{ $pensioner->full_name }}">
                                                        <i class="fas fa-trash-alt me-1"></i> Delete Record
                                                    </button>
                                                </li>';

$newButtonPart = "@can('manage_pensioners')
                                                    <li><hr class=\"dropdown-divider\"></li>
                                                    <li>
                                                        <button type=\"button\" class=\"dropdown-item text-danger\" 
                                                                onclick=\"requestPensionerDeletion('{{ \$pensioner->id }}', '{{ addslashes(\$pensioner->full_name) }}')\">
                                                            <i class=\"fas fa-trash-alt me-1\"></i> Delete Record
                                                        </button>
                                                    </li>
                                                @endcan";

// Try a more robust regex-based replacement for the button block
$pattern = '/<li><hr class="dropdown-divider"><\/li>\s+<li>\s+<button type="button" class="dropdown-item text-danger delete-pensioner-btn"\s+data-id="\{\{ \$pensioner->id \}\}"\s+data-name="\{\{ \$pensioner->full_name \}\}">\s+<i class="fas fa-trash-alt me-1"><\/i> Delete Record\s+<\/button>\s+<\/li>/';

if (preg_match($pattern, $content)) {
    $content = preg_replace($pattern, $newButtonPart, $content);
    echo "Button block updated via regex.\n";
} else {
    // Fallback simple search
    $content = str_replace('delete-pensioner-btn', '', $content);
    echo "Fallback: removed class 'delete-pensioner-btn'.\n";
}

file_put_contents($file, $content);
echo "File updated.\n";
