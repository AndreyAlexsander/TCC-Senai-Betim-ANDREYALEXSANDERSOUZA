let produtosCache = [];

window.addEventListener('DOMContentLoaded', async () => {
    if (!(await requireLogin())) return;
    await carregarProdutos();
    document.getElementById('buscaProdutos').addEventListener('input', debounce(renderTabela));
    document.getElementById('statusProdutos').addEventListener('change', renderTabela);
});

async function carregarProdutos() {
    const response = await fetch('php/produtos.php');
    const data = await response.json();
    produtosCache = data.produtos || [];
    renderTabela();
}

function renderTabela() {
    const tbody = document.getElementById('productsTable');
    const busca = document.getElementById('buscaProdutos').value.toLowerCase();
    const status = document.getElementById('statusProdutos').value;
    const produtos = produtosCache.filter(produto => {
        const texto = `${produto.nome} ${produto.categoria} ${produto.responsavel || ''}`.toLowerCase();
        return (!busca || texto.includes(busca)) && (!status || produto.status === status);
    });

    document.getElementById('productsCount').textContent = `${produtos.length} produto(s)`;
    if (!produtos.length) {
        tbody.innerHTML = '<tr><td colspan="7">Nenhum produto encontrado.</td></tr>';
        return;
    }

    tbody.innerHTML = produtos.map(produto => `
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
    if (!confirm('Excluir este produto e seu histórico?')) return;
    const body = new URLSearchParams({ id });
    const response = await fetch('php/deletar_produto.php', { method: 'POST', body });
    const data = await response.json();
    if (data.sucesso) {
        showToast('Produto excluido.');
        await carregarProdutos();
    } else {
        alert(data.mensagem || 'Erro ao excluir.');
    }
}
