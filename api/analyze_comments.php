<?php
require_once '../config/database.php'; // Configuración de OpenAI API

function analyze_comment($comment) {
    $api_key = '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';
    $url = 'https://api.openai.com/v1/completions';

    $data = [
        "model" => "gpt-4",
        "prompt" => "Analiza el siguiente comentario relacionado con una cotización:\n\n$comment",
        "max_tokens" => 150,
    ];

    $options = [
        'http' => [
            'header'  => "Content-type: application/json\r\nAuthorization: Bearer $api_key",
            'method'  => 'POST',
            'content' => json_encode($data),
        ],
    ];

    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);

    return json_decode($response, true);
}

// Procesar comentarios y enviarlos a OpenAI
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $comment = sanitize_input($_POST['comment']);
    $analysis = analyze_comment($comment);
    echo json_encode($analysis);
    exit;
}
?>

<script>
    async function analyzeComment(comment) {
        try {
            const response = await fetch('analyze_comments.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ comment })
            });
            const analysis = await response.json();
            console.log('Análisis del comentario:', analysis);
        } catch (error) {
            console.error('Error al analizar el comentario:', error);
        }
    }
</script>
