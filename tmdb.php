<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Filme (TMDb)</title>
</head>
<body>
    <h1>API Filme (TMDb)</h1>

    <?php
    function conectarBanco() {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "tmdb";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Conexão falhou: " . $conn->connect_error);
        }

        return $conn;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Dados do formulário
        $nomeFilme = $_POST["nome_filme"];

        // Chave da API
        $api_key = "ec5687a803680497b9f65ba8836e2b77";

        // API TMDb
        $url = "https://api.themoviedb.org/3/search/movie?api_key={$api_key}&query=" . urlencode($nomeFilme);

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);
        $data = json_decode($response, true);
        $conn = conectarBanco();

        if ($conn->connect_error) {
            die("Conexão falhou: " . $conn->connect_error);
        }

        if (isset($data['results'][0])) {
            $filme = $data['results'][0];
            
            $inserirFilme = $conn->prepare("INSERT INTO filmes (titulo, descricao, ano_lancamento) VALUES (?, ?, ?)");

            if ($inserirFilme === false) {
                die("Erro na preparação da declaração: " . $conn->error);
            }

            $inserirFilme->bind_param("sss", $filme['title'], $filme['overview'], $filme['release_date']);
            if ($inserirFilme === false) {
                die("Erro na vinculação dos parâmetros: " . $conn->error);
            }

            $inserirFilme->execute();

            if ($inserirFilme === false) {
                die("Erro na execução da declaração: " . $conn->error);
            }
            $conn->close();

            // Exibe os resultados
            echo "<h2>Dados do Filme:</h2>";
            echo "Título:</strong> {$filme['title']}<br><br>";
            echo "Descrição:</strong> {$filme['overview']}<br><br>";
            echo "Ano de Lançamento:</strong> {$filme['release_date']}<br><br>";
        } else {
            echo "Filme não encontrado.<br>";
        }
    }
    ?>

    <form method="post" action="">
        <label for="nome_filme">Nome do Filme:</label>
        <input type="text" name="nome_filme" required>
        
        <button type="submit">Procurar</button>
    </form>
</body>
</html>
