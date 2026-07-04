const produtoId = new URLSearchParams(location.search).get('id');

window.addEventListener('DOMContentLoaded', async () => {
    if (!(await requireLogin())) return;
    if (produtoId) await carregarProduto();
    document.getElementById('formProduto').addEventListener('submit', salvarProduto);
});

async function carregarProduto() {
    const response = await fetch('php/produtos.php');
    const data = await response.json();
    const produto = (data.produtos || []).find(item => Number(item.id) === Number(produtoId));
    if (!produto) return;

    document.getElementById('tituloProduto').textContent = 'Editar produto';
    document.getElementById('subtituloProduto').textContent = 'Atualize os dados';

    const form = document.getElementById('formProduto');
    Object.keys(produto).forEach(key => {
        if (form.elements[key]) form.elements[key].value = produto[key] || '';
    });
}

async function salvarProduto(event) {
    event.preventDefault();
    const form = event.currentTarget;
    const button = document.getElementById('btnSalvar');
    const message = document.getElementById('mensagemProduto');
    button.disabled = true;
    button.textContent = 'Salvando...';
    message.textContent = '';

    try {
        const response = await fetch(produtoId ? 'php/atualizar_produto.php' : 'php/cadastro_produto.php', {
            method: 'POST',
            body: new FormData(form)
        });
        const data = await response.json();
        if (!data.sucesso) throw new Error(data.mensagem || 'Erro ao salvar.');
        message.textContent = produtoId ? 'Produto atualizado com sucesso.' : 'Produto salvo com sucesso.';
        message.className = 'form-message success';
        setTimeout(() => window.location.href = 'produtos.html', 700);
    } catch (error) {
        message.textContent = error.message;
        message.className = 'form-message error';
    } finally {
        button.disabled = false;
        button.textContent = produtoId ? 'Atualizar produto' : 'Salvar produto';
    }
}
