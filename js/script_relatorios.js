let graficoRelatorio = null;
let relatorioAtual = { produtos: [], stats: {} };

window.addEventListener('DOMContentLoaded', async () => {
    if (await requireLogin()) gerarRelatorio();
});

async function gerarRelatorio() {
    const filtros = new URLSearchParams({
        dataInicio: document.getElementById('dataInicio').value,
        dataFim: document.getElementById('dataFim').value,
        status: document.getElementById('filtroRelatorio').value
    });

    const resposta = await fetch(`php/relatorios.php?${filtros.toString()}`);
    relatorioAtual = await resposta.json();

    atualizarResumo(relatorioAtual.stats || {});
    atualizarTabela(relatorioAtual.produtos || []);
    renderGrafico(relatorioAtual.stats || {});
}

function atualizarResumo(resumo) {
    const emAndamento = Number(resumo.desenvolvimento || 0)
        + Number(resumo.teste || 0)
        + Number(resumo.aprovacao || 0);

    document.getElementById('totalRelatorio').textContent = resumo.total || 0;
    document.getElementById('progressoRelatorio').textContent = emAndamento;
    document.getElementById('concluidosRelatorio').textContent = resumo.lancado || 0;
    document.getElementById('potencialRelatorio').textContent = formatCurrency(resumo.potencial || 0);
}

function atualizarTabela(produtos) {
    const tbody = document.querySelector('#tabelaRelatorio tbody');
    if (!produtos.length) {
        tbody.innerHTML = '<tr><td colspan="5">Nenhum resultado encontrado.</td></tr>';
        return;
    }

    tbody.innerHTML = produtos.map(produto => `
        <tr>
            <td>${escapeHtml(produto.nome)}</td>
            <td>${statusPill(produto.status)}</td>
            <td>${escapeHtml(produto.categoria)}</td>
            <td>${escapeHtml(produto.responsavel || '-')}</td>
            <td>${formatCurrency(produto.potencial_receita)}</td>
        </tr>
    `).join('');
}

function renderGrafico(resumo) {
    const grafico = document.getElementById('graficoRelatorio');

    if (graficoRelatorio) graficoRelatorio.destroy();

    graficoRelatorio = new Chart(grafico, {
        type: 'bar',
        data: {
            labels: STATUS_ORDER.map(statusLabel),
            datasets: [{
                label: 'Produtos',
                data: STATUS_ORDER.map(status => Number(resumo[status] || 0)),
                backgroundColor: ['#b45309', '#0e7490', '#ca8a04', '#4f46e5', '#15803d', '#64748b']
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });
}

function exportarPDF() {
    if (!window.jspdf) {
        alert('A biblioteca de PDF não carregou.');
        return;
    }

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();

    doc.setFontSize(18);
    doc.text('Relatório KAION', 14, 18);
    doc.setFontSize(10);
    doc.text(`Gerado em ${new Date().toLocaleDateString('pt-BR')}`, 14, 27);
    doc.text(`Total: ${relatorioAtual.stats.total || 0}`, 14, 38);
    doc.text(`Potencial: ${formatCurrency(relatorioAtual.stats.potencial || 0)}`, 14, 45);

    let y = 58;
    relatorioAtual.produtos.slice(0, 28).forEach(produto => {
        doc.text(`${produto.nome} | ${statusLabel(produto.status)} | ${formatCurrency(produto.potencial_receita)}`.substring(0, 92), 14, y);
        y += 7;
    });
    doc.save(`relatorio-kaion-${Date.now()}.pdf`);
}

function exportarExcel() {
    const filtros = new URLSearchParams({
        dataInicio: document.getElementById('dataInicio').value,
        dataFim: document.getElementById('dataFim').value,
        status: document.getElementById('filtroRelatorio').value
    });

    window.location.href = `php/exportar_excel.php?${filtros.toString()}`;
}
