async function cadastrar(){ // funcao assincrona, ou seja aceita await dentro pra pagina nao travar

    var username = document.getElementById("username").value;
    var email    = document.getElementById("email").value;
    var password = document.getElementById("password").value;

    var jsonData = {
        username: username,
        email: email,
        password: password
    };

    var dados = await sendHybrid("/ProgramacaoWEB/TDE_ProgramacaoWEB/php/cadastrar.php", jsonData);

    console.log(dados);

    if (dados.status === 's'){
        alert(dados.mensagem);
        window.location.href = "/ProgramacaoWEB/TDE_ProgramacaoWEB/paginaLogin/index.html";
    } else {
        alert(dados.mensagem);
    }
}