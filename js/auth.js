const messages = {
    credenciais: 'Email ou senha incorretos.',
    servidor: 'Não foi possível concluir a operação agora.',
    email_existe: 'Este email já está cadastrado.',
    email_invalido: 'Informe um email válido.',
    senha_fraca: 'A senha precisa ter pelo menos 6 caracteres.',
    senhas_diferentes: 'As senhas não conferem.',
    cadastro: 'Conta criada. Entre com seu email e senha.'
};

window.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const key = params.get('error') || params.get('success');
    const target = document.getElementById('mensagemLogin')
        || document.getElementById('mensagemCadastro');

    if (!target || !messages[key]) return;
    target.textContent = messages[key];
    target.className = `form-message ${params.get('error') ? 'error' : 'success'}`;
});
