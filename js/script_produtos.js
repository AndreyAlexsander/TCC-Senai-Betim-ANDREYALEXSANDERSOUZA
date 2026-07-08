let produtosCache = [];

window.addEventListener('DOMContentLoaded', async () => {
    if (!(await requireLogin())) return;
    await carregarProdutos();
    document.getElementById('buscaProdutos').addEventListener('input', debounce(renderTabela));
    document.getElementById('statusProdutos').addEventListener('change', renderTabela);
});

async function carregarProdutos() {
    const resposta = await fetch('php/produtos.php');
    const dados = await resposta.json();

    produtosCache = dados.produtos || [];
    renderTabela();
}

function renderTabela() {
    const tabela = document.getElementById('productsTable');
    const busca = document.getElementById('buscaProdutos').value.toLowerCase();
    const status = document.getElementById('statusProdutos').value;

    const produtos = produtosCache.filter(produto => {
        const texto = `${produto.nome} ${produto.categoria} ${produto.responsavel || ''}`.toLowerCase();
        return (!busca || texto.includes(busca)) && (!status || produto.status === status);
    });

    document.getElementById('productsCount').textContent = `${produtos.length} produto(s)`;

    if (!produtos.length) {
        tabela.innerHTML = '<tr><td colspan="7">Nenhum produto encontrado.</td></tr>';
        return;
    }

    tabela.innerHTML = produtos.map(produto => `
        <tr>
            <td><strong>${escapeHtml(produto.nome)}</strong><br><span class="table-note">${escapeHtml(produto.mercado_alvo || '')}</span></td>
            <td>${escapeHtml(produto.categoria)}</td>
            <td>${statusPill(produto.status)}</td>
            <td>${escapeHtml(produto.prioridade || 'media')}</td>
            <td>${escapeHtml(produto.responsavel || '-')}</td>
            <td>${formatDate(produto.data_atualizacao || produto.data_criacao)}</td>
            <td>
                <a class="btn small" href="novo_produto.html?id=${Number(produto.id)}">Editar</a>
                <button class="btn danger" type="button" onclick="deletarProduto(${Number(produto.id)})">Excluir</button>
            </td>
        </tr>
    `).join('');
}

async function deletarProduto(id) {
    if (!confirm('Tem certeza que deseja excluir este produto e o histórico dele?')) return;

    const corpo = new URLSearchParams({ id });
    const resposta = await fetch('php/deletar_produto.php', { method: 'POST', body: corpo });
    const dados = await resposta.json();

    if (dados.sucesso) {
        showToast('Produto excluído.');
        await carregarProdutos();
    } else {
        alert(dados.mensagem || 'Erro ao excluir.');
    }
}
