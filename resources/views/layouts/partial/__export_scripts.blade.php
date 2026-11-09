<script src="{{ asset('themes/backend/plugins/report-export/html2canvas.min.js') }}"></script>
<script src="{{ asset('themes/backend/plugins/report-export/jspdf.umd.min.js') }}"></script>
<script>
    function getprint(printAreaId, fileName) {
        document.title = fileName;
        var printContent = document.getElementById(printAreaId).innerHTML;
        var originalContent = document.body.innerHTML;
        document.body.innerHTML = printContent;
        window.print();
        document.body.innerHTML = originalContent;
        window.location.reload();
    }

    async function shareReport(printAreaId, fileName) {
        const shareBtn = document.getElementById('shareBtn');
        const originalText = shareBtn.innerHTML;
        shareBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
        shareBtn.classList.add('disabled');
        
        try {
            const element = document.getElementById(printAreaId);
            const originalPadding = element.style.padding;
            const originalMinHeight = element.style.minHeight;
            element.style.padding = '35px 25px 35px 25px';
            element.style.minHeight = '1123px'; // Standard A4 height at 96 DPI

            const canvas = await html2canvas(element, {
                scale: 2, 
                useCORS: true,
                backgroundColor: '#ffffff'
            });
            
            element.style.padding = originalPadding;
            element.style.minHeight = originalMinHeight;

            canvas.toBlob(async function(blob) {
                const file = new File([blob], fileName + '.png', { type: 'image/png' });

                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    try {
                        await navigator.share({
                            title: fileName,
                            text: 'Please find the attached Report.',
                            files: [file],
                        });
                    } catch (err) {
                        console.log('Share cancelled or failed', err);
                    }
                } else {
                    alert('Your browser or device does not support direct file sharing. Please use a mobile phone or modern browser.');
                }
                shareBtn.innerHTML = originalText;
                shareBtn.classList.remove('disabled');
            }, 'image/png');
        } catch (error) {
            console.error('Error generating image:', error);
            alert('Something went wrong while generating the report image.');
            shareBtn.innerHTML = originalText;
            shareBtn.classList.remove('disabled');
        }
    }

    async function downloadImage(printAreaId, fileName) {
        const downloadBtn = document.getElementById('downloadBtn');
        const originalText = downloadBtn.innerHTML;
        downloadBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
        downloadBtn.classList.add('disabled');
        
        try {
            const element = document.getElementById(printAreaId);
            const originalPadding = element.style.padding;
            const originalMinHeight = element.style.minHeight;
            element.style.padding = '35px 25px 35px 25px';
            element.style.minHeight = '1123px'; // Standard A4 height at 96 DPI

            const canvas = await html2canvas(element, {
                scale: 2, 
                useCORS: true,
                backgroundColor: '#ffffff'
            });
            
            element.style.padding = originalPadding;
            element.style.minHeight = originalMinHeight;

            const link = document.createElement('a');
            link.download = fileName + '.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
            
            downloadBtn.innerHTML = originalText;
            downloadBtn.classList.remove('disabled');
        } catch (error) {
            console.error('Error generating image:', error);
            alert('Something went wrong while downloading the report image.');
            downloadBtn.innerHTML = originalText;
            downloadBtn.classList.remove('disabled');
        }
    }

    async function downloadPDF(printAreaId, fileName) {
        const downloadPdfBtn = document.getElementById('downloadPdfBtn');
        const originalText = downloadPdfBtn.innerHTML;
        downloadPdfBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
        downloadPdfBtn.classList.add('disabled');
        
        try {
            const element = document.getElementById(printAreaId);
            const originalPadding = element.style.padding;
            const originalMinHeight = element.style.minHeight;
            element.style.padding = '35px 25px 35px 25px';
            element.style.minHeight = '1123px'; // Standard A4 height at 96 DPI

            const canvas = await html2canvas(element, {
                scale: 2, 
                useCORS: true,
                backgroundColor: '#ffffff'
            });
            
            element.style.padding = originalPadding;
            element.style.minHeight = originalMinHeight;

            const imgData = canvas.toDataURL('image/png');
            
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF('p', 'mm', 'a4');
            
            const pdfWidth = pdf.internal.pageSize.getWidth();
            const pdfHeight = (canvas.height * pdfWidth) / canvas.width;
            
            pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
            pdf.save(fileName + '.pdf');
            
            downloadPdfBtn.innerHTML = originalText;
            downloadPdfBtn.classList.remove('disabled');
        } catch (error) {
            console.error('Error generating PDF:', error);
            alert('Something went wrong while downloading the PDF.');
            downloadPdfBtn.innerHTML = originalText;
            downloadPdfBtn.classList.remove('disabled');
        }
    }
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof jQuery !== 'undefined' && jQuery('.zoom-gallery').length > 0 && typeof jQuery.fn.magnificPopup !== 'undefined') {
            jQuery('.zoom-gallery').magnificPopup({
                delegate: 'a',
                type: 'image',
                closeOnContentClick: false,
                closeBtnInside: false,
                mainClass: 'mfp-with-zoom mfp-img-mobile',
                image: {
                    verticalFit: true,
                    titleSrc: function(item) {
                        return item.el.attr('title');
                    }
                },
                gallery: {
                    enabled: true
                },
                zoom: {
                    enabled: true,
                    duration: 300,
                    opener: function(element) {
                        return element.find('img');
                    }
                }
            });
        }
    });
</script>
