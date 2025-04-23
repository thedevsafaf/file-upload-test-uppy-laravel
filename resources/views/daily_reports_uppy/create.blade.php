<!DOCTYPE html>
<html>
<head>
    <title>Uppy File Upload – Daily Report</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://releases.transloadit.com/uppy/v3.14.1/uppy.min.css" rel="stylesheet">
    <script src="https://releases.transloadit.com/uppy/v3.14.1/uppy.min.js"></script>

    <style>
        body { font-family: Arial, sans-serif; padding: 2rem; max-width: 700px; margin: auto; }
        form, #uppy { margin-top: 1rem; }
    </style>
</head>
<body>

    <h2>Submit Daily Report with Uppy</h2>

    <form id="reportForm">
        <label for="report_date">Report Date:</label>
        <input type="date" name="report_date" id="report_date" required>
        <input type="hidden" name="uploaded_files" id="uploaded_files">
        <button type="submit" style="margin-top: 1rem;">Submit Report</button>
    </form>

    <div id="uppy" style="margin-top: 2rem;"></div>

    <p id="responseMsg" style="margin-top: 1rem; font-weight: bold;"></p>

    <script>
        const uppy = new Uppy.Uppy({
            restrictions: {
                maxNumberOfFiles: 20,
                maxFileSize: 1024 * 1024 * 100, // 100 MB
            },
            autoProceed: true
        });

        uppy.use(Uppy.Dashboard, {
            inline: true,
            target: '#uppy',
            note: 'Upload images and docs (max 100MB each)',
            proudlyDisplayPoweredByUppy: false
        });

        uppy.use(Uppy.XHRUpload, {
            endpoint: "{{ route('daily-reports.uppy-upload') }}",
            fieldName: 'file',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        let uploadedFiles = [];

        uppy.on('upload-success', (file, response) => {
            uploadedFiles.push(response.body.path);
        });

        document.getElementById('reportForm').addEventListener('submit', async function (e) {
            e.preventDefault();

            const form = e.target;
            const formData = new FormData();
            formData.append('report_date', form.report_date.value);
            formData.append('uploaded_files', JSON.stringify(uploadedFiles));

            const res = await fetch("{{ route('daily-reports.store-uppy') }}", {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            const data = await res.json();
            const responseMsg = document.getElementById('responseMsg');
            if (res.ok) {
                responseMsg.style.color = 'green';
                responseMsg.textContent = data.message;
                form.reset();
                uploadedFiles = [];
                uppy.reset();
            } else {
                responseMsg.style.color = 'red';
                responseMsg.textContent = data.message || 'Upload failed';
            }
        });
    </script>

</body>
</html>
