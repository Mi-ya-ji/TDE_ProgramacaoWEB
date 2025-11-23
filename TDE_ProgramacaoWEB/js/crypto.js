function wordArrayToBase64(wordArray) {
    return CryptoJS.enc.Base64.stringify(wordArray);
}

async function getServerPublicKey() {
    const res = await fetch("/ProgramacaoWEB/TDE_ProgramacaoWEB/php/public_key.php");
    const j = await res.json();
    return j.public_key;
}

function generateAes() {
    const key = CryptoJS.lib.WordArray.random(32); 
    const iv  = CryptoJS.lib.WordArray.random(16); 
    return { key, iv };
}

function encryptAES(jsonData, key, iv) {
    const plaintext = JSON.stringify(jsonData);
    
    const encrypted = CryptoJS.AES.encrypt(plaintext, key, {
        iv: iv,
        mode: CryptoJS.mode.CBC,
        padding: CryptoJS.pad.Pkcs7
    });
    
    return CryptoJS.enc.Base64.stringify(encrypted.ciphertext);
}

function encryptAESKeyWithRSA(aesKey, publicKeyPem) {
    const js = new JSEncrypt();
    js.setPublicKey(publicKeyPem);

    const aesKeyB64 = CryptoJS.enc.Base64.stringify(aesKey);
    return js.encrypt(aesKeyB64);
}

async function sendHybrid(url, jsonBody) {

    const publicKey = await getServerPublicKey();
    const { key, iv } = generateAes();

    const ciphertext = encryptAES(jsonBody, key, iv);
    const encryptedAESKey = encryptAESKeyWithRSA(key, publicKey);

    const payload = {
        ciphertext: ciphertext,
        encrypted_key: encryptedAESKey,
        iv: wordArrayToBase64(iv)
    };

    const res = await fetch(url, {
        method: "POST",
        headers: { "Content-Type": "application/json"},
        body: JSON.stringify(payload)
    });

    const responseText = await res.text();
    console.log("Resposta do servidor:", responseText);
    
    try {
        return JSON.parse(responseText);
    } catch (e) {
        console.error("Erro ao parsear JSON:", e);
        console.error("Conteúdo recebido:", responseText);
        return { status: 'e', mensagem: 'Erro: resposta inválida do servidor' };
    }
}