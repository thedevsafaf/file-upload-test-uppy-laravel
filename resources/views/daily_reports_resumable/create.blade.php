<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Resumable Upload – Daily Report</title>
    <script src="https://cdn.jsdelivr.net/npm/resumablejs/resumable.js"></script>
    <style>
        #resumable-drop {
            border: 2px dashed #ccc;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            margin-top: 20px;
        }
        .file-item {
            margin: 10px 0;
            color: green;
        }
    </style>
</head>
<body>
    <h2>Upload Daily Report Documents (Resumable)</h2>

    <form id="reportForm">
        <label for="report_date">Report Date:</label>
        <input type="date" name="report_date" required><br><br>

        <div id="resumable-drop">
            <p>Drag & drop documents here or click to select</p>
        </div>

        <div id="uploadList"></div>

        <button type="submit">Submit Report</button>
    </form>

    <script>
        const uploadList = document.getElementById('uploadList');
        const uploadedImagePaths = [];
        const uploadedDocPaths = [];

        const r = new Resumable({
            target: "{{ route('daily_reports.resumable.upload') }}",
            query: {
                _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            chunkSize: 1 * 1024 * 1024, // 1MB
            simultaneousUploads: 3,
            testChunks: false
        });

        r.assignBrowse(document.getElementById('resumable-drop'));
        r.assignDrop(document.getElementById('resumable-drop'));

        r.on('fileAdded', function(file) {
            r.upload();
            const el = document.createElement('div');
            el.id = `file-${file.uniqueIdentifier}`;
            el.innerText = `Uploading ${file.fileName}...`;
            uploadList.appendChild(el);
        });

        r.on('fileSuccess', function(file, response) {
            const data = JSON.parse(response);
            const fileType = file.file.type;

            // Show success message
            document.getElementById(`file-${file.uniqueIdentifier}`).innerText = `✅ Uploaded: ${data.name}`;

            // Separate image/doc
            if (fileType.startsWith('image/')) {
                uploadedImagePaths.push(data.path);
            } else {
                uploadedDocPaths.push(data.path);
            }
        });

        r.on('fileError', function(file, message) {
             // Show error message
            document.getElementById(`file-${file.uniqueIdentifier}`).innerText = `❌ Failed: ${message}`;
        });

        document.getElementById('reportForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const form = e.target;
            const formData = new FormData();
            formData.append('report_date', form.report_date.value);
            formData.append('uploaded_images', JSON.stringify(uploadedImagePaths));
            formData.append('uploaded_docs', JSON.stringify(uploadedDocPaths));

            const res = await fetch("{{ route('daily-reports.store') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await res.json();
            if (res.ok) {
                alert(data.message);
                form.reset();
                uploadList.innerHTML = '';
                uploadedImagePaths.length = 0;
                uploadedDocPaths.length = 0;
            } else {
                alert(data.message || 'Something went wrong.');
            }
        });
    </script>
</body>
</html>
