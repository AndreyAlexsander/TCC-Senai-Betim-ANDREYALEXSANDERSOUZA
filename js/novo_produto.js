const produtoId = new URLSearchParams(location.search).get('id');

window.addEventListener('DOMContentLoaded', async () => {
    if (!(await requireLogin())) return;
    if (produtoId) await carregarProduto();
    document.getElementById('formProduto').addEventListener('submit', salvarProduto);
});

async function carregarProduto() {
    const resposta = await fetch('php/produtos.php');
    const dados = await resposta.json();
    const produto = (dados.produtos || []).find(item => Number(item.id) === Number(produtoId));
    if (!produto) return;

    document.getElementById('tituloProduto').textContent = 'Editar produto';
    document.getElementById('subtituloProduto').textContent = 'Atualize os dados';

    const formulario = document.getElementById('formProduto');
    Object.keys(produto).forEach(key => {
        if (formulario.elements[key]) formulario.elements[key].value = produto[key] || '';
    });
}

async function salvarProduto(event) {
    event.preventDefault();
    const formulario = event.currentTarget;
    const botao = document.getElementById('btnSalvar');
    const mensagem = document.getElementById('mensagemProduto');

    botao.disabled = true;
    botao.textContent = 'Salvando...';
    mensagem.textContent = '';

    try {
        const pagina = produtoId ? 'php/atualizar_produto.php' : 'php/cadastro_produto.php';
        const resposta = await fetch(pagina, {
            method: 'POST',
            body: new FormData(formulario)
        });

        const dados = await resposta.json();
        if (!dados.sucesso) throw new Error(dados.mensagem || 'Erro ao salvar.');

        mensagem.textContent = produtoId ? 'Produto atualizado com sucesso.' : 'Produto salvo com sucesso.';
        mensagem.className = 'form-message success';
        setTimeout(() => window.location.href = 'produtos.html', 700);
    } catch (error) {
        mensagem.textContent = error.message;
        mensagem.className = 'form-message error';
    } finally {
        botao.disabled = false;
        botao.textContent = produtoId ? 'Atualizar produto' : 'Salvar produto';
    }
}
