<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Resumable Upload – Daily Report</title>
    <script src="https://cdn.jsdelivr.net/npm/resumablejs/resumable.js"></script>
    <style>
        /* Style for the drop zones */
        #image-drop, #doc-drop {
            border: 2px groove #737e7e;
            background: #f9fafb;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            margin-top: 20px;
            border-radius: 12px;
            transition: background 0.3s, border-color 0.3s;
            font-family: 'Inter', sans-serif;
            color: #6b7280;
            font-size: 18px;
        }

        #image-drop:hover, #doc-drop:hover {
            background: #eef2ff;
            border-color: #6366f1;
        }

        .file-item {
            margin: 12px 0;
            padding: 10px 15px;
            background: #ecfdf5;
            color: #065f46;
            border-left: 4px solid #10b981;
            border-radius: 8px;
            font-size: 16px;
            font-family: 'Inter', sans-serif;
            transition: background 0.3s;
        }

        .file-item:hover {
            background: #d1fae5;
        }

    </style>
</head>
<body>
    <h2>Upload Daily Report Documents - CHUNKS (Resumable)</h2>

    <form id="reportForm">
        <label for="report_date">Report Date:</label>
        <input type="date" name="report_date" required><br><br>

        <!-- Image Drop Area -->
        <div id="image-drop">
            <p>Drag & drop images here or click to select images</p>
        </div>

        <!-- Document Drop Area -->
        <div id="doc-drop">
            <p>Drag & drop documents here or click to select documents</p>
        </div>

        <div id="uploadList"></div>

        <button type="submit">Submit Report</button>
    </form>

    <script>
        const uploadList = document.getElementById('uploadList');
        const uploadedImagePaths = [];
        const uploadedDocPaths = [];

        // Create two separate Uppy instances for image and doc uploads
        const imageUploader = new Resumable({
            target: "{{ route('daily_reports.resumable.chunks.upload') }}",
            query: {
                _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            chunkSize: 1 * 1024 * 1024, // 1MB
            simultaneousUploads: 3,
            testChunks: false
        });

        const docUploader = new Resumable({
            target: "{{ route('daily_reports.resumable.chunks.upload') }}",
            query: {
                _token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            chunkSize: 1 * 1024 * 1024, // 1MB
            simultaneousUploads: 3,
            testChunks: false
        });

        // Assign drop areas
        imageUploader.assignBrowse(document.getElementById('image-drop'));
        imageUploader.assignDrop(document.getElementById('image-drop'));

        docUploader.assignBrowse(document.getElementById('doc-drop'));
        docUploader.assignDrop(document.getElementById('doc-drop'));

        // Handle file additions
        imageUploader.on('fileAdded', function(file) {
            imageUploader.upload();
            const el = document.createElement('div');
            el.id = `file-${file.uniqueIdentifier}`;
            el.innerText = `Uploading ${file.fileName}...`;
            uploadList.appendChild(el);
        });

        docUploader.on('fileAdded', function(file) {
            docUploader.upload();
            const el = document.createElement('div');
            el.id = `file-${file.uniqueIdentifier}`;
            el.innerText = `Uploading ${file.fileName}...`;
            uploadList.appendChild(el);
        });

        // Handle successful file uploads
        imageUploader.on('fileSuccess', function(file, response) {
            const data = JSON.parse(response);
            document.getElementById(`file-${file.uniqueIdentifier}`).innerText = `✅ Uploaded: ${data.name}`;
            uploadedImagePaths.push(data.path);
        });

        docUploader.on('fileSuccess', function(file, response) {
            const data = JSON.parse(response);
            document.getElementById(`file-${file.uniqueIdentifier}`).innerText = `✅ Uploaded: ${data.name}`;
            uploadedDocPaths.push(data.path);
        });

        // Handle file errors
        imageUploader.on('fileError', function(file, message) {
            document.getElementById(`file-${file.uniqueIdentifier}`).innerText = `❌ Failed: ${message}`;
        });

        docUploader.on('fileError', function(file, message) {
            document.getElementById(`file-${file.uniqueIdentifier}`).innerText = `❌ Failed: ${message}`;
        });

        // Handle form submission
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
