jQuery(function($) {
    let $fileInput = $('#excel-upload-file');
    let $csvDelimiter = $('#csv-delimiter');
    let $utf8Bom = $('#csv-utf-8-bom');
    let $fileInputLabel = $('#excel-upload-file-label');
    let $uploadButton = $('#start-upload-button');
    let $uploadForm = $('#upload-form');
    let $errorAlert = $('#error-alert');
    let $loadingScreen = $('#loading-screen');
    let $loadingText = $('#loading-text');

    $fileInput.on('change', function() {
        $fileInputLabel.text($(this).val());
    });

    $uploadButton.on('click', function() {
        displayUploadForm(false);
        displayLoadingScreen(true);
        if(!$fileInput.val().length) {
            displayLoadingScreen(false);
            displayUploadForm(true, 'Please select a excel-file!');
            return;
        }

        let xhr = new XMLHttpRequest();
        let formData = new FormData();
        formData.append('file', $fileInput[0].files[0]);
        let utf8BomData = true;
        if(!$utf8Bom.is(':checked')) {
            utf8BomData = false;
        }
        formData.append('includeUTF8Bom', utf8BomData);
        formData.append('delimiter', $csvDelimiter.val());
        xhr.open('POST', '/api/upload');
        xhr.upload.addEventListener('progress', function (event) {
            if (event.lengthComputable) {
                let percent = Math.floor((event.loaded / event.total) * 100);
                $loadingText.text('Uploading (' + percent + '%)');
            }
        }, false);
        xhr.onreadystatechange = function(e) {
            if (this.readyState === 4 ) {
                if(this.status === 200) {
                    let response = JSON.parse(this.response);
                    if(response.success) {
                        checkingJobStatus(response.job_id);
                    }
                    else {
                        displayLoadingScreen(false);
                        displayUploadForm(true, 'Server-Error! Please try later again.');
                    }
                }
                else if(this.status === 400) {
                    let response = JSON.parse(this.response);
                    displayLoadingScreen(false);
                    displayUploadForm(true, response.message);
                }
                else {
                    displayLoadingScreen(false);
                    displayUploadForm(true, 'Server-Error! Please try later again.');
                }
            }
        };
        xhr.send(formData);
    });

    let jobStatusInterval;
    function checkingJobStatus(jobID) {
        $loadingText.text('Waiting for processing ...');
        jobStatusInterval = setInterval(function () {
            requestJobStatus(jobID);
        }, 10000);
        requestJobStatus(jobID);
    }

    function requestJobStatus(jobID) {
        let xhr = new XMLHttpRequest();
        xhr.open('GET', '/api/status/' + jobID);
        xhr.timeout = 8000;
        xhr.onreadystatechange = function(e) {
            if (this.readyState === 4 ) {
                if(this.status === 200) {
                    let response = JSON.parse(this.response);
                    if(response.success) {
                        switch (response.status) {
                            case 'in_progress':
                                $loadingText.text('File is in process ...');
                                break;
                            case 'finished':
                                clearInterval(jobStatusInterval);
                                window.location.replace('/share/' + jobID);
                                break;
                            case 'failed':
                                clearInterval(jobStatusInterval);
                                displayLoadingScreen(false);
                                displayUploadForm(true, 'Failed to parse your excel-file! Please check your file!');
                                break;
                        }
                    }
                    else {
                        console.log('Failed to check job-status!');
                        displayLoadingScreen(false);
                        displayUploadForm(true, 'Server-Error! Please try later again.');
                    }
                }
                else {
                    displayLoadingScreen(false);
                    displayUploadForm(true, 'Server-Error! Please try later again.');
                }
            }
        };
        xhr.send();
    }

    function displayUploadForm(show, errorMessage) {
        $errorAlert.addClass('d-none');
        if(show) {
            $uploadForm.css('display', 'block');
            if(errorMessage.length) {
                $errorAlert.removeClass('d-none');
                $errorAlert.text(errorMessage);
            }
        }
        else {
            $uploadForm.css('display', 'none');
        }
    }

    function displayLoadingScreen(show) {
        if(show) {
            $loadingScreen.removeClass('d-none');
        }
        else {
            $loadingScreen.addClass('d-none');
        }
    }
});
