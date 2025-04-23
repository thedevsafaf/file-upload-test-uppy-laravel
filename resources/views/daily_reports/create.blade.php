<!DOCTYPE html>
<html>
<head>
    <title>Daily Report Submission</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 2rem;
            max-width: 600px;
            margin: auto;
        }
        label {
            display: block;
            margin-top: 1rem;
        }
        input, button {
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>

    <h1>Submit Daily Report</h1>

    <form id="reportForm">
        <label for="report_date">Report Date:</label>
        <input type="date" name="report_date" required>

        <label for="report_images">Report Images:</label>
        <input type="file" name="report_images[]" multiple accept="image/*">

        <label for="report_docs">Report Documents:</label>
        <input type="file" name="report_docs[]" multiple accept=".pdf,.doc,.docx,.txt">

        <button type="submit">Submit Report</button>
    </form>

    <p id="responseMsg" style="margin-top: 1rem; font-weight: bold;"></p>

    <script>
        document.getElementById('reportForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const form = e.target;
            const formData = new FormData(form);

            const responseMsg = document.getElementById('responseMsg');
            responseMsg.textContent = "Uploading...";

            try {
                let response = await fetch("{{ route('daily-reports.store') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: formData
                });

                let data = await response.json();

                if (response.ok) {
                    responseMsg.style.color = 'green';
                    responseMsg.textContent = data.message;
                    form.reset();
                } else {
                    responseMsg.style.color = 'red';
                    responseMsg.textContent = data.message || 'Failed to submit';
                }
            } catch (error) {
                responseMsg.style.color = 'red';
                responseMsg.textContent = 'Upload failed. Please try again.';
                console.error(error);
            }
        });
    </script>

</body>
</html>
