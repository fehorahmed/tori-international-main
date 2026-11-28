<script>
// Export each numbered statement page separately so a long report is not clipped.
async function downloadPDF(printAreaId, fileName) {
    const button = document.getElementById('downloadPdfBtn');
    const original = button.innerHTML;
    button.disabled = true;
    button.classList.add('disabled');
    button.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
    try {
        await document.fonts.ready;
        const pages = document.querySelectorAll('#' + printAreaId + ' .report-page');
        if (!pages.length) return;
        const pdf = new window.jspdf.jsPDF('l', 'mm', 'a4');
        for (let index = 0; index < pages.length; index++) {
            const canvas = await html2canvas(pages[index], {
                scale: 2, useCORS: true, backgroundColor: '#ffffff', windowWidth: 1200,
                onclone: function (doc) { doc.querySelector('.report-wrap').style.width = '1120px'; }
            });
            if (index) pdf.addPage();
            const width = pdf.internal.pageSize.getWidth() - 16;
            const height = pdf.internal.pageSize.getHeight() - 16;
            const scale = Math.min(width / canvas.width, height / canvas.height);
            pdf.addImage(canvas.toDataURL('image/png'), 'PNG', 8, 8, canvas.width * scale, canvas.height * scale);
        }
        pdf.save(fileName + '.pdf');
    } catch (error) {
        console.error('Ledger PDF export failed', error);
        alert('Unable to export PDF. Please try again.');
    } finally {
        button.disabled = false;
        button.classList.remove('disabled');
        button.innerHTML = original;
    }
}
</script>
