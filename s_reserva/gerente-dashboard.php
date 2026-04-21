<?php
$conn = new mysqli("localhost", "root", "", "restaurante");

if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}
/* =========================
   APROVAR RESERVA
========================= */
if (isset($_GET['aprovar'])) {
    $id = intval($_GET['aprovar']);
    $conn->query("UPDATE reservas SET estado='Aprovada' WHERE id=$id");
    header("Location: gerente-dashboard.php");
    exit();
}

/* =========================
   CANCELAR RESERVA
========================= */
if (isset($_GET['cancelar'])) {
    $id = intval($_GET['cancelar']);
    $conn->query("UPDATE reservas SET estado='Cancelada' WHERE id=$id");
    header("Location: gerente-dashboard.php");
    exit();
}

/* =========================
   EXCLUIR RESERVA
========================= */
if (isset($_GET['excluir'])) {
    $id = intval($_GET['excluir']);
    $conn->query("DELETE FROM reservas WHERE id=$id");
    header("Location: gerente-dashboard.php");
    exit();
}

/* =========================
   ADICIONAR PRATO
========================= */
if (isset($_POST['add_prato'])) {
    $nome = $conn->real_escape_string($_POST['nome']);
    $preco = floatval($_POST['preco']);
    $qtdprato = intval($_POST['qtdprato']); // corrigido

    if (!file_exists('uploads')) {
        mkdir('uploads', 0777, true);
    }

    $imagem_nome = '';

    if (!empty($_FILES['imagem']['name'])) {
        $imagem_nome = time() . '_' . basename($_FILES['imagem']['name']);
        move_uploaded_file($_FILES['imagem']['tmp_name'], 'uploads/' . $imagem_nome);
    }

    $conn->query("
        INSERT INTO pratos (nome, preco, qtdprato, imagem)
        VALUES ('$nome', $preco, $qtdprato, '$imagem_nome')
    ");

    header("Location: gerente-dashboard.php");
    exit();
}

/* =========================
   EDITAR PRATO
========================= */
if (isset($_POST['editar_prato'])) {
    $id = intval($_POST['id']);
    $nome = $conn->real_escape_string($_POST['nome']);
    $preco = floatval($_POST['preco']);
    $qtdprato = intval($_POST['quantidade']);

    $conn->query("
        UPDATE pratos 
        SET nome='$nome', preco=$preco, qtdprato=$quantidade 
        WHERE id=$id
    ");

    header("Location: gerente-dashboard.php#pratos");
    exit();
}

/* =========================
   EXCLUIR PRATO
========================= */
if (isset($_GET['excluir_prato'])) {
    $id = intval($_GET['excluir_prato']);
    $conn->query("DELETE FROM pratos WHERE id=$id");
    header("Location: gerente-dashboard.php#pratos");
    exit();
}

/* =========================
   BUSCAR DADOS
========================= */
$reservas = $conn->query("SELECT * FROM reservas ORDER BY id DESC");
$pratos = $conn->query("SELECT * FROM pratos ORDER BY id DESC");

$total = $conn->query("SELECT COUNT(*) as total FROM reservas")->fetch_assoc()['total'];
$aprovadas = $conn->query("SELECT COUNT(*) as total FROM reservas WHERE estado='Aprovada'")->fetch_assoc()['total'];
$canceladas = $conn->query("SELECT COUNT(*) as total FROM reservas WHERE estado='Cancelada'")->fetch_assoc()['total'];
$total_pratos = $conn->query("SELECT COUNT(*) as total FROM pratos")->fetch_assoc()['total'];

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Painel Gerente</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial, sans-serif;
}

body{
    background:linear-gradient(135deg,#111,#2a0000);
    padding:20px;
    color:white;
}

.dashboard-container{
    max-width:1400px;
    margin:auto;
}

.top-menu{
    background:rgba(0,0,0,0.85);
    border:1px solid rgba(255,255,255,0.08);
    border-radius:18px;
    padding:18px 25px;
    margin-bottom:25px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    flex-wrap:wrap;
    gap:20px;
}

.menu-logo{
    font-size:24px;
    font-weight:bold;
}

.menu-logo span{
    color:#ff2b2b;
}

.menu-links{
    display:flex;
    gap:15px;
    flex-wrap:wrap;
}

.menu-links a{
    text-decoration:none;
    color:white;
    padding:10px 18px;
    border-radius:12px;
    font-size:14px;
    font-weight:600;
    transition:.3s;
}

.menu-links a:hover,
.menu-links a.active{
    background:#ff2b2b;
}

.stats-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
    margin-bottom:30px;
}

.card{
    background:rgba(255,255,255,0.95);
    color:black;
    padding:25px;
    border-radius:18px;
}

.card h3{
    font-size:14px;
    margin-bottom:10px;
}

.card p{
    font-size:34px;
    font-weight:bold;
    color:#ff2b2b;
}

.section{
    background:rgba(255,255,255,0.96);
    color:black;
    padding:25px;
    border-radius:18px;
    margin-bottom:25px;
}

.section h2{
    margin-bottom:20px;
    color:#111;
}

.form-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(200px,1fr));
    gap:15px;
    margin-bottom:20px;
}

input{
    width:100%;
    padding:12px;
    border:1px solid #ddd;
    border-radius:10px;
}

button{
    background:#ff2b2b;
    color:white;
    border:none;
    padding:12px 20px;
    border-radius:10px;
    cursor:pointer;
    font-weight:bold;
}

button:hover{
    background:#d90000;
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    background:#111;
    color:white;
    padding:14px;
    text-align:left;
}

td{
    padding:14px;
    border-bottom:1px solid #eee;
}

.btn{
    text-decoration:none;
    padding:8px 12px;
    border-radius:8px;
    color:white;
    font-size:12px;
    display:inline-block;
    margin:2px;
}

.aprovar{background:green;}
.cancelar{background:orange;}
.excluir{background:#111;}

.prato-box{
    border:1px solid #eee;
    border-radius:14px;
    padding:15px;
    margin-bottom:15px;
}

.prato-box img{
    width:100%;
    max-width:250px;
    border-radius:12px;
    margin-bottom:10px;
}
</style>
</head>
<body>

<div class="dashboard-container">

    <div class="top-menu">
        <div class="menu-logo">Restaurante <span>MM</span></div>
        <div class="menu-links">
            <a href="#" class="active">Dashboard</a>
            <a href="#reservas">Reservas</a>
            <a href="cardapio.html">Cardápio</a>
            <a href="index.html">Sair</a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="card">
            <h3>Total Reservas</h3>
            <p><?php echo $total; ?></p>
        </div>

        <div class="card">
            <h3>Aprovadas</h3>
            <p><?php echo $aprovadas; ?></p>
        </div>

        <div class="card">
            <h3>Canceladas</h3>
            <p><?php echo $canceladas; ?></p>
        </div>

        <div class="card">
            <h3>Pratos</h3>
            <p><?php echo $total_pratos; ?></p>
        </div>
    </div>

    <div class="section" id="pratos">
        <h2>Adicionar Prato</h2>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <input type="text" name="nome" placeholder="Nome do prato" required>
                <input type="number" step="0.01" name="preco" placeholder="Preço" required>
                <input type="number" name="quantidade" placeholder="Quantidade" required>
                <input type="file" name="imagem" required>
            </div>

            <button type="submit" name="add_prato">Adicionar Prato</button>
        </form>
    </div>

    <div class="section" id="reservas">
        <h2>Reservas</h2>

        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Telefone</th>
                    <th>Data</th>
                    <th>Hora</th>
                    <th>Estado</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $reservas->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $row['nome']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td><?php echo $row['telefone']; ?></td>
                    <td><?php echo $row['data_reserva']; ?></td>
                    <td><?php echo $row['hora_reserva']; ?></td>
                    <td><?php echo $row['estado']; ?></td>
                    <td>
                        <a class="btn aprovar" href="?aprovar=<?php echo $row['id']; ?>">Aprovar</a>
                        <a class="btn cancelar" href="?cancelar=<?php echo $row['id']; ?>">Cancelar</a>
                        <a class="btn excluir" href="?excluir=<?php echo $row['id']; ?>">Excluir</a>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <div class="section">
     <?php while($prato = $pratos->fetch_assoc()) { ?>
<div class="prato-box">

    <?php if($prato['imagem']) { ?>
        <img src="uploads/<?php echo $prato['imagem']; ?>" alt="">
    <?php } ?>

    <form method="POST">
        <input type="hidden" name="id" value="<?php echo $prato['id']; ?>">

        <h3>
            <input type="text" name="nome" value="<?php echo $prato['nome']; ?>">
        </h3>

        <p>
            <strong>Preço:</strong>
            <input type="number" step="0.01" name="preco" value="<?php echo $prato['preco']; ?>">
        </p>

        <p>
            <strong>Quantidade:</strong>
            <input type="number" name="quantidade" value="<?php echo $prato['qtdprato']; ?>">
        </p>

        <button type="submit" name="editar_prato">Salvar</button>

        <a class="btn excluir" href="?excluir_prato=<?php echo $prato['id']; ?>">
            Excluir
        </a>
    </form>

</div>
<?php } ?>
    </div>

</div>

</body>
</html>