<!DOCTYPE html>
<html>
<head>
    <title>Uppy Separate Uploads – Daily Report</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://releases.transloadit.com/uppy/v3.14.1/uppy.min.css" rel="stylesheet">
    <script src="https://releases.transloadit.com/uppy/v3.14.1/uppy.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; padding: 2rem; max-width: 700px; margin: auto; }
        form, .uppy-container { margin-top: 1.5rem; }
    </style>
</head>
<body>

    <h2>Submit Daily Report (Separate Images & Docs)</h2>

    <form id="reportForm">
        <label for="report_date">Report Date:</label>
        <input type="date" name="report_date" id="report_date" required><br><br>

        <h3>Upload Images</h3>
        <div id="uppyImages" class="uppy-container"></div>

        <h3>Upload Documents</h3>
        <div id="uppyDocs" class="uppy-container"></div>

        <input type="hidden" name="uploaded_images" id="uploaded_images">
        <input type="hidden" name="uploaded_docs" id="uploaded_docs">

        <button type="submit" style="margin-top: 1rem;">Submit Report</button>
    </form>

    <p id="responseMsg" style="margin-top: 1rem; font-weight: bold;"></p>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Uppy instance for Images
        const uppyImages = new Uppy.Uppy({
            restrictions: {
                maxNumberOfFiles: 10,
                maxFileSize: 100 * 1024 * 1024, // 100 MB
                allowedFileTypes: ['image/jpeg', 'image/png', 'image/jpg', 'image/gif']
            },
            autoProceed: true
        });

        uppyImages.use(Uppy.Dashboard, {
            inline: true,
            target: '#uppyImages',
            note: 'Upload only images (jpg, png, gif)',
            proudlyDisplayPoweredByUppy: false
        });

        uppyImages.use(Uppy.XHRUpload, {
            endpoint: "{{ route('daily-reports.chunks.uppy-upload') }}",
            fieldName: 'file',
            headers: { 'X-CSRF-TOKEN': csrfToken }
        });

        const uploadedImages = [];

        uppyImages.on('upload-success', (file, response) => {
            uploadedImages.push(response.body.path);
        });

        // Uppy instance for Documents
        const uppyDocs = new Uppy.Uppy({
            restrictions: {
                maxNumberOfFiles: 10,
                maxFileSize: 100 * 1024 * 1024, // 100 MB
                allowedFileTypes: ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain']
            },
            autoProceed: true
        });

        uppyDocs.use(Uppy.Dashboard, {
            inline: true,
            target: '#uppyDocs',
            note: 'Upload only documents (pdf, doc, docx, txt)',
            proudlyDisplayPoweredByUppy: false
        });

        uppyDocs.use(Uppy.XHRUpload, {
            endpoint: "{{ route('daily-reports.chunks.uppy-upload') }}",
            fieldName: 'file',
            headers: { 'X-CSRF-TOKEN': csrfToken }
        });

        const uploadedDocs = [];

        uppyDocs.on('upload-success', (file, response) => {
            uploadedDocs.push(response.body.path);
        });

        // Form Submission
        document.getElementById('reportForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData();
            formData.append('report_date', document.getElementById('report_date').value);
            formData.append('uploaded_images', JSON.stringify(uploadedImages));
            formData.append('uploaded_docs', JSON.stringify(uploadedDocs));

            const res = await fetch("{{ route('daily-reports.chunks.store-uppy') }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                }
            });

            const data = await res.json();
            const responseMsg = document.getElementById('responseMsg');
            if (res.ok) {
                responseMsg.style.color = 'green';
                responseMsg.textContent = data.message;
                this.reset();
                uploadedImages.length = 0;
                uploadedDocs.length = 0;
                uppyImages.reset();
                uppyDocs.reset();
            } else {
                responseMsg.style.color = 'red';
                responseMsg.textContent = data.message || 'Upload failed';
            }
        });
    </script>

</body>
</html>
