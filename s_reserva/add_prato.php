if (isset($_POST['add_prato'])) {

    $nome = $_POST['nome'];
    $preco = $_POST['preco'];
    $quantidade = $_POST['quantidade'];

    /* upload imagem */
    $imagem = $_FILES['imagem']['name'];
    $tmp = $_FILES['imagem']['tmp_name'];

    $pasta = "uploads/";
    if (!file_exists($pasta)) {
        mkdir($pasta);
    }

    move_uploaded_file($tmp, $pasta.$imagem);

    /* inserir no banco */
    $stmt = $conn->prepare("
        INSERT INTO pratos (nome, preco, quantidade, imagem)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->bind_param("sdis", $nome, $preco, $quantidade, $imagem);

    $stmt->execute();
}