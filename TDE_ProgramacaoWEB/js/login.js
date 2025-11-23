async function login(){ // funcao assincrona, ou seja aceita await dentro pra pagina nao travar

    var username = document.getElementById("username").value;
    var password = document.getElementById("password").value;

    var jsonData = {
        username: username,
        password: password
    };

    var dados = await sendHybrid("/ProgramacaoWEB/TDE_ProgramacaoWEB/php/login.php", jsonData);

    console.log(dados)

    if (dados.status === 's'){
        alert(dados.mensagem);
        window.location.href = "../paginaInicial/index.html";
    } else {
        alert(dados.mensagem)
    }
}