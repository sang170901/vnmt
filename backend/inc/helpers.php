<?php
/**
 * Helper functions for backend.
 */
function find_supplier_id(PDO $pdo, ?string $productSlug, ?string $manufacturer): ?int {
    // normalize
    $productSlug = strtolower(trim((string)$productSlug));
    $manufacturer = strtolower(trim((string)$manufacturer));

    // try exact supplier slug
    if ($productSlug) {
        $parts = preg_split('/[-_\.\/]+/', $productSlug);
        foreach ($parts as $part) {
            if (strlen($part) < 3) continue;
            $stmt = $pdo->prepare('SELECT id FROM suppliers WHERE LOWER(slug) = ? LIMIT 1');
            $stmt->execute([$part]);
            $r = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($r) return (int)$r['id'];
        }
        // try contains
        $stmt = $pdo->prepare('SELECT id FROM suppliers WHERE LOWER(slug) LIKE ? LIMIT 1');
        $stmt->execute(['%'.$productSlug.'%']);
        if ($r = $stmt->fetch(PDO::FETCH_ASSOC)) return (int)$r['id'];
    }

    // try manufacturer matching to supplier name or slug
    if ($manufacturer) {
        $stmt = $pdo->prepare('SELECT id FROM suppliers WHERE LOWER(slug)=? OR LOWER(name) LIKE ? LIMIT 1');
        $stmt->execute([$manufacturer, '%'.$manufacturer.'%']);
        if ($r = $stmt->fetch(PDO::FETCH_ASSOC)) return (int)$r['id'];
    }

    return null;
}

if (!function_exists('removeVietnameseAccents')) {
    /**
     * Convert Vietnamese characters to ASCII equivalents.
     */
    function removeVietnameseAccents(string $str): string
    {
        $accents = [
            'à' => 'a', 'á' => 'a', 'ạ' => 'a', 'ả' => 'a', 'ã' => 'a',
            'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ậ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a',
            'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ặ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a',
            'è' => 'e', 'é' => 'e', 'ẹ' => 'e', 'ẻ' => 'e', 'ẽ' => 'e',
            'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ệ' => 'e', 'ể' => 'e', 'ễ' => 'e',
            'ì' => 'i', 'í' => 'i', 'ị' => 'i', 'ỉ' => 'i', 'ĩ' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ọ' => 'o', 'ỏ' => 'o', 'õ' => 'o',
            'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ộ' => 'o', 'ổ' => 'o', 'ỗ' => 'o',
            'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ợ' => 'o', 'ở' => 'o', 'ỡ' => 'o',
            'ù' => 'u', 'ú' => 'u', 'ụ' => 'u', 'ủ' => 'u', 'ũ' => 'u',
            'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ự' => 'u', 'ử' => 'u', 'ữ' => 'u',
            'ỳ' => 'y', 'ý' => 'y', 'ỵ' => 'y', 'ỷ' => 'y', 'ỹ' => 'y',
            'đ' => 'd',
            'À' => 'A', 'Á' => 'A', 'Ạ' => 'A', 'Ả' => 'A', 'Ã' => 'A',
            'Â' => 'A', 'Ầ' => 'A', 'Ấ' => 'A', 'Ậ' => 'A', 'Ẩ' => 'A', 'Ẫ' => 'A',
            'Ă' => 'A', 'Ằ' => 'A', 'Ắ' => 'A', 'Ặ' => 'A', 'Ẳ' => 'A', 'Ẵ' => 'A',
            'È' => 'E', 'É' => 'E', 'Ẹ' => 'E', 'Ẻ' => 'E', 'Ẽ' => 'E',
            'Ê' => 'E', 'Ề' => 'E', 'Ế' => 'E', 'Ệ' => 'E', 'Ể' => 'E', 'Ễ' => 'E',
            'Ì' => 'I', 'Í' => 'I', 'Ị' => 'I', 'Ỉ' => 'I', 'Ĩ' => 'I',
            'Ò' => 'O', 'Ó' => 'O', 'Ọ' => 'O', 'Ỏ' => 'O', 'Õ' => 'O',
            'Ô' => 'O', 'Ồ' => 'O', 'Ố' => 'O', 'Ộ' => 'O', 'Ổ' => 'O', 'Ỗ' => 'O',
            'Ơ' => 'O', 'Ờ' => 'O', 'Ớ' => 'O', 'Ợ' => 'O', 'Ở' => 'O', 'Ỡ' => 'O',
            'Ù' => 'U', 'Ú' => 'U', 'Ụ' => 'U', 'Ủ' => 'U', 'Ũ' => 'U',
            'Ư' => 'U', 'Ừ' => 'U', 'Ứ' => 'U', 'Ự' => 'U', 'Ử' => 'U', 'Ữ' => 'U',
            'Ỳ' => 'Y', 'Ý' => 'Y', 'Ỵ' => 'Y', 'Ỷ' => 'Y', 'Ỹ' => 'Y',
            'Đ' => 'D'
        ];

        return strtr($str, $accents);
    }
}

if (!function_exists('createSlug')) {
    /**
     * Generate a slug from provided text.
     */
    function createSlug(string $text): string
    {
        $text = trim($text);
        $text = removeVietnameseAccents($text);
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        $text = trim($text, '-');
        return $text;
    }
}

if (!function_exists('ensureUniqueSlug')) {
    /**
     * Ensure slug is unique within a table by appending incremental index.
     */
    function ensureUniqueSlug(PDO $pdo, string $baseSlug, string $table, string $column = 'slug'): string
    {
        $slug = $baseSlug ?: 'item';
        $original = $slug;
        $counter = 1;

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM {$table} WHERE {$column} = ?");
        while (true) {
            $stmt->execute([$slug]);
            if ($stmt->fetchColumn() == 0) {
                break;
            }
            $slug = $original . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}