<?php

class WPM2AWS_Migrate
{
    public function __construct()
    {
        if (!function_exists('get_plugin_data')) {
            add_action('admin_init', array($this, 'pluginData'));
        }
    }

    public function pluginData()
    {
        require_once(get_home_path() . 'wp-admin/includes/plugin.php');
    }


    public function registerLicence()
    {
        add_action('admin_post_wpm2aws_register_licence_form', array($this, 'registerLicenceForm'));
    }


    public function validateInputs()
    {
        add_action('admin_post_wpm2aws_iam_form', array($this, 'saveIamForm'));
    }


    public function registerLicenceForm()
    {
        $validatePost = wpm2awsValidatePost('register-licence-form');
        if ($validatePost === false) {
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), __('Error!<br><br>Licence Key<br><br>Invalid/Incomplete Input', 'migrate-2-aws'));
            exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
        }

        $requiredInputs = array(
            'wpm2aws-licence-key' => $_POST['wpm2aws_licence_key'],
            'wpm2aws-licence-email' => $_POST['wpm2aws_licence_email'],
        );
        $validatedInputs = wpm2awsValidateSanitizeInputs($requiredInputs);
        if (empty($validatedInputs)) {
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), __('Error!<br><br>Licence Key<br><br>Required Input is Empty', 'migrate-2-aws'));
            exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
        }

        // Register The Licence
        $registered = $this->registerSeahorseLicence($validatedInputs);

        if (!$registered) {
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), __('Error!<br><br>Licence Key - Invalid Licence Key/Email', 'migrate-2-aws'));
            exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
        }
        set_transient('wpm2aws_admin_success_notice_' . get_current_user_id(), __('Success!<br><br>Licence Key - Registered', 'migrate-2-aws'));
        exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
    }



    public function saveIamForm()
    {
        $validatePost = wpm2awsValidatePost('iam-form');
        if ($validatePost === false) {
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), __('Error!<br><br>IAM Credentials<br><br>Invalid/Incomplete Input', 'migrate-2-aws'));
            exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
        }

        $requiredInputs = array(
            'wpm2aws-iamid' => $_POST['wpm2aws_iamid'],
            'wpm2aws-iampw' => $_POST['wpm2aws_iampw']
        );
        $validatedInputs = wpm2awsValidateSanitizeInputs($requiredInputs);
        if (empty($validatedInputs)) {
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), __('Error!<br><br>IAM Credentials<br><br>Required Input is Empty', 'migrate-2-aws'));
            exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
        }


        // Set the Options
        foreach ($validatedInputs as $valInKey => $valInVal) {
            wpm2awsAddUpdateOptions($valInKey, $valInVal);
        }

        // Check if this is a valid User
        $user = $this->verifyIam();

        // Set the User Name Option
        if ($user) {
            $validatedInputs['wpm2aws-iam-user'] = $user;
            // Set the Options
            wpm2awsAddUpdateOptions('wpm2aws-iam-user', $user);
            wpm2awsLogRAction('wpm2aws_validate_iam_user_success', $user);



            // Get a List of the Users Existing Buckets
            if (get_option('wpm2aws-customer-type') === 'self') {
                // exit('Self Migration');
                $this->getBuckets(true);
            }

            // Create a Default Bucket Name for User
            $awsResourceName = WPM2AWS_PLUGIN_AWS_RESOURCE . '-' . strtolower(get_option('wpm2aws-iam-user'));
            $awsLightsailName = strtolower(get_option('wpm2aws-iam-user')) . '-' . WPM2AWS_PLUGIN_AWS_RESOURCE;
            wpm2awsAddUpdateOptions('wpm2aws-aws-s3-default-bucket-name', $awsResourceName);

            if (
                false !==  get_option('wpm2aws_valid_licence_type') &&
                'TRIAL' === strtoupper(get_option('wpm2aws_valid_licence_type'))
            ) {
                $userRef = str_replace('@', '-', get_option('wpm2aws_licence_email'));
                $userRef = str_replace('.', '-', $userRef);
                
                $awsResourceName .= '-' . $userRef;
                $awsLightsailName .= '-' . $userRef;
                wpm2awsAddUpdateOptions('wpm2aws-aws-s3-default-bucket-name', $awsResourceName);
            }

            
            // Adjust For Max Length = 63
            if (strlen($awsResourceName) > 63) {
                $awsResourceName = substr($awsResourceName, 0, 63);
                $lastChar = substr($awsResourceName, -1);
                while ('-' === $lastChar) {
                    $awsResourceName = substr($awsResourceName, 0, (strlen($awsResourceName) - 1));
                    $lastChar = substr($awsResourceName, -1);
                }
                wpm2awsAddUpdateOptions('wpm2aws-aws-s3-default-bucket-name', $awsResourceName);
            }

            if (get_option('wpm2aws-customer-type') === 'managed') {
                // Set AWS Region as the Default
                wpm2awsAddUpdateOptions('wpm2aws-aws-region', WPM2AWS_PLUGIN_AWS_REGION);

                // Create A Bucket
                $bucket = $this->createBucket();

                if ($bucket) {
                    // Update Bucket Name
                    wpm2awsAddUpdateOptions('wpm2aws-aws-s3-bucket-name', $awsResourceName);
                    wpm2awsLogRAction('wpm2aws_create_bucket_success', $awsResourceName);

                    // sns trigger to send mail after S3 bucket created
                    try {
                        $this->triggerSNSAlert(get_option('wpm2aws-aws-region'), 'S3', $user);
                    } catch (Exception $e) {
                        wpm2awsLogAction('Error: saveIamForm->triggerSNSAlert: ' . $e->getMessage());
                    }

                    // Set the Name of the AWS Instance
                    wpm2awsAddUpdateOptions('wpm2aws-aws-lightsail-name', $awsLightsailName);
                    // Set the Region of the AWS Instance
                    wpm2awsAddUpdateOptions('wpm2aws-aws-lightsail-region', WPM2AWS_PLUGIN_AWS_REGION);

                    // Set the AWS Region as per Default Region

                    wpm2awsAddUpdateOptions('wpm2aws_current_active_step', 2);
                }
            }

            // Set the Admin Notice
            set_transient('wpm2aws_admin_success_notice_' . get_current_user_id(), __('Success!<br><br>IAM Credentials Validated<br><br>IAM User: ' . $user, 'migrate-2-aws'));

        // }


        // Set the Admin Notice
        // if ($user) {
        //     set_transient('wpm2aws_admin_success_notice_' . get_current_user_id(), __('Success!<br><br>IAM Credentials Validated<br><br>IAM User: ' . $user, 'migrate-2-aws'));
        } else {
            wpm2awsLogRAction('wpm2aws_validate_iam_user_error', "Invalid IAM Credentials");
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), __('Error!<br><br>Invalid IAM Credentials<br><br>Please Try Again', 'migrate-2-aws'));
        }


        exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
    }


    public function addUpdateRegion()
    {
        add_action('admin_post_wpm2aws_aws_region', array($this, 'saveAwsRegion'));
    }

    public function saveAwsRegion()
    {
        $validatePost = wpm2awsValidatePost('aws-region');
        if ($validatePost === false) {
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), __('Error!<br><br>Set AWS Region<br><br>Invalid/Incomplete Input', 'migrate-2-aws'));
            exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
            // wp_die('Fn::saveAwsRegion<br><br>Invalid Post Data (Invalid Post)<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }

        $requiredInputs = array(
            'wpm2aws-aws-region' => $_POST['wpm2aws_awsRegionSelect']
        );
        $validatedInputs = wpm2awsValidateSanitizeInputs($requiredInputs);
        if (empty($validatedInputs)) {
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), __('Error!<br><br>Set AWS Region<br><br>Required Input is Empty', 'migrate-2-aws'));
            exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
            // wp_die('Invalid Post Data (Empty Input)<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }

        // Also Update AWS Region
        $validatedInputs['wpm2aws-aws-lightsail-region'] = $validatedInputs['wpm2aws-aws-region'];

        foreach ($validatedInputs as $valInKey => $valInVal) {
            wpm2awsAddUpdateOptions($valInKey, $valInVal);
        }
        wpm2awsLogRAction('wpm2aws_save_region_success', $validatedInputs['wpm2aws-aws-lightsail-region']);
        exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
    }

    public function addUpdateS3BucketName()
    {
        // $this->saveIamForm();
        add_action('admin_post_wpm2aws_s3_bucket', array($this, 'saveS3Bucket'));
        add_action('admin_post_wpm2aws_s3_create_bucket', array($this, 'saveS3Bucket'));
        add_action('admin_post_wpm2aws_s3_use_bucket', array($this, 'saveS3Bucket'));
        // do_action('admin_post_wpm2aws_iam_form');
    }

    public function saveS3Bucket()
    {
        $validatePost = wpm2awsValidatePost('aws-s3-existing-bucket');
        if ($validatePost === true) {
            $this->useS3Bucket();
            return;
        }


        $validatePost = wpm2awsValidatePost('aws-s3-bucket-name');
        if ($validatePost === false) {
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), __('Error!<br><br>Set S3 Bucket Name<br><br>Invalid/Incomplete Input', 'migrate-2-aws'));
            exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
            // wp_die('Fn::saveS3Bucket<br><br>Invalid Post Data (Invalid Post)<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }

        $requiredInputs = array(
            'wpm2aws-aws-s3-bucket-name' => $_POST['wpm2aws_s3BucketName']
        );
        $validatedInputs = wpm2awsValidateSanitizeInputs($requiredInputs);
        if (empty($validatedInputs)) {
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), __('Error!<br><br>Set S3 Bucket Name<br><br>Required Input is Empty', 'migrate-2-aws'));
            exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
            // wp_die('Invalid Post Data (Empty Input)<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }
        if (!wpm2awsValidateBucketName($_POST['wpm2aws_s3BucketName'])) {
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), __('Error!<br><br>Set S3 Bucket Name<br><br>Must Conform to URI Standards', 'migrate-2-aws'));
            exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
            // wp_die('Invalid Post Data (Empty Input)<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }

        // Set the Options
        foreach ($validatedInputs as $valInKey => $valInVal) {
            wpm2awsAddUpdateOptions($valInKey, $valInVal);
        }

        // create the bucket
        $bucket = $this->createBucket(false);

        // Get a List of the Users Existing Buckets
        if ($bucket) {
            $this->getBuckets();
            // sns trigger to send mail after S3 bucket created
            try {
                $this->triggerSNSAlert(get_option('wpm2aws-aws-region'), 'S3', $user);
            } catch (Exception $e) {
                wpm2awsLogAction('Error: saveS3Bucket->triggerSNSAlert: ' . $e->getMessage());
            }
        }

        // Set the Admin Notice
        if ($bucket) {
            wpm2awsLogRAction('wpm2aws_bucket_created_success', $bucket);
            set_transient('wpm2aws_admin_success_notice_' . get_current_user_id(), __('Success!<br><br>New Bucket Created<br><br>Bucket Name: ' . $user, 'migrate-2-aws'));
        } else {
            wpm2awsLogRAction('wpm2aws_bucket_created_fail', 'Invalid Bucket Details');
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), __('Error!<br><br>Invalid Bucket Details<br><br>Please Try Again', 'migrate-2-aws'));
        }

        exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
    }

    private function useS3Bucket()
    {
        $validatePost = wpm2awsValidatePost('aws-s3-existing-bucket');
        if ($validatePost === false) {
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), __('Error!<br><br>Use S3 Bucket<br><br>Invalid/Incomplete Input', 'migrate-2-aws'));
            exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
            // wp_die('Fn::useS3Bucket<br><br>Invalid Post Data (Invalid Post)<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }

        $requiredInputs = array(
            'wpm2aws-aws-s3-existing-bucket' => $_POST['wpm2aws_s3BucketNameExisting'],
            'wpm2aws-aws-s3-bucket-name' => $_POST['wpm2aws_s3BucketNameExisting']
        );
        $validatedInputs = wpm2awsValidateSanitizeInputs($requiredInputs);
        if (empty($validatedInputs)) {
            wpm2awsLogRAction('wpm2aws_use_bucket_fail', 'Use S3 Bucket - Required Input is Empty');
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), __('Error!<br><br>Use S3 Bucket<br><br>Required Input is Empty', 'migrate-2-aws'));
            exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
            // wp_die('Invalid Post Data (Empty Input)<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }
        foreach ($validatedInputs as $valInKey => $valInVal) {
            wpm2awsAddUpdateOptions($valInKey, $valInVal);
        }

        wpm2awsLogRAction('wpm2aws_use_bucket_success', 'Bucket Name: ' . $validatedInputs['wpm2aws-aws-s3-bucket-name']);
        exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
    }


    public function setS3UploadName()
    {
        // $this->saveIamForm();
        add_action('admin_post_wpm2aws_upload-directory-name', array($this, 'setUploadName'));
        // do_action('admin_post_wpm2aws_iam_form');
    }

    public function setUploadName()
    {
        $validatePost = wpm2awsValidatePost('aws-s3-upload-name');
        if ($validatePost === false) {
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), __('Error!<br><br>Upload Directory Name<br><br>Invalid/Incomplete Input', 'migrate-2-aws'));
            exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
            // wp_die('Fn::setUploadName<br><br>Invalid Post Data (Invalid Post)<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }

        $requiredInputs = array(
            'wpm2aws-aws-s3-upload-directory-name' => $_POST['wpm2aws_uploadDirectoryName']
        );
        $validatedInputs = wpm2awsValidateSanitizeInputs($requiredInputs);
        if (empty($validatedInputs)) {
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), __('Error!<br><br>Upload Directory Name<br><br>Required Input is Empty', 'migrate-2-aws'));
            exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
            // wp_die('Invalid Post Data (Empty Input)<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }
        foreach ($validatedInputs as $valInKey => $valInVal) {
            wpm2awsAddUpdateOptions($valInKey, $valInVal);
        }
        exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
    }

    public function setS3UploadPath()
    {
        // $this->saveIamForm();
        add_action('admin_post_wpm2aws_upload-directory-path', array($this, 'setUploadPath'));
        // do_action('admin_post_wpm2aws_iam_form');
    }

    public function setUploadPath()
    {
        $validatePost = wpm2awsValidatePost('aws-s3-upload-path');
        if ($validatePost === false) {
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), __('Error!<br><br>Upload Directory Path<br><br>Invalid/Incomplete Input', 'migrate-2-aws'));
            exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
            // wp_die('Fn::setUploadPath<br><br>Invalid Post Data (Invalid Post)<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }

        $requiredInputs = array(
            'wpm2aws-aws-s3-upload-directory-path' => $_POST['wpm2aws_uploadDirectoryPath']
        );
        $validatedInputs = wpm2awsValidateSanitizeInputs($requiredInputs);
        if (empty($validatedInputs)) {
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), __('Error!<br><br>Upload Directory Path<br><br>Required Input is Empty', 'migrate-2-aws'));
            exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
            // wp_die('Invalid Post Data (Empty Input)<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }
        foreach ($validatedInputs as $valInKey => $valInVal) {
            wpm2awsAddUpdateOptions($valInKey, $valInVal);
        }
        exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
    }

    public function addUpdateLightsailName()
    {
        // $this->saveIamForm();
        add_action('admin_post_wpm2aws_lightsail-name', array($this, 'saveLightsailName'));
        // do_action('admin_post_wpm2aws_iam_form');
    }

    public function saveLightsailName()
    {
        $validatePost = wpm2awsValidatePost('aws-lightsail-name');
        if ($validatePost === false) {
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), __('Error!<br><br>AWS Instance Name<br><br>Invalid/Incomplete Input', 'migrate-2-aws'));
            exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
            // wp_die('Fn::saveLightsailName<br><br>Invalid Post Data (Invalid Post)<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }

        $requiredInputs = array(
            'wpm2aws-aws-lightsail-name' => $_POST['wpm2aws_lightsailName']
        );
        $validatedInputs = wpm2awsValidateSanitizeInputs($requiredInputs);
        if (empty($validatedInputs)) {
            wpm2awsLogRAction('wpm2aws_set_lightsail_name_error', 'AWS Instance Name - Required Input is Empty');
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), __('Error!<br><br>AWS Instance Name<br><br>Required Input is Empty', 'migrate-2-aws'));
            exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
            // wp_die('Invalid Post Data (Empty Input)<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }
        foreach ($validatedInputs as $valInKey => $valInVal) {
            wpm2awsAddUpdateOptions($valInKey, $valInVal);
        }
        wpm2awsLogRAction('wpm2aws_set_lightsail_name_success', 'AWS Name: ' . $validatedInputs['wpm2aws-aws-lightsail-name']);
        exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
    }


    public function addUpdateLightsailRegion()
    {
        // $this->saveIamForm();
        add_action('admin_post_wpm2aws_lightsail-region', array($this, 'saveLightsailRegion'));
        // do_action('admin_post_wpm2aws_iam_form');
    }

    public function saveLightsailRegion()
    {
        $validatePost = wpm2awsValidatePost('aws-lightsail-region');
        if ($validatePost === false) {
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), __('Error!<br><br>AWS Region<br><br>Invalid/Incomplete Input', 'migrate-2-aws'));
            exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
            // wp_die('Fn::saveLightsailRegion<br><br>Invalid Post Data (Invalid Post)<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }

        $requiredInputs = array(
            'wpm2aws-aws-lightsail-region' => $_POST['wpm2aws_lightsailRegionSelect']
        );
        $validatedInputs = wpm2awsValidateSanitizeInputs($requiredInputs);
        if (empty($validatedInputs)) {
            wpm2awsLogRAction('wpm2aws_set_lightsail_region_error', 'AWS Instance Region - Required Input is Empty');
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), __('Error!<br><br>AWS Region<br><br>Required Input is Empty', 'migrate-2-aws'));
            exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
            // wp_die('Invalid Post Data (Empty Input)<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }
        foreach ($validatedInputs as $valInKey => $valInVal) {
            wpm2awsAddUpdateOptions($valInKey, $valInVal);
        }
        wpm2awsLogRAction('wpm2aws_set_lightsail_region_success', 'AWS Region: ' . $validatedInputs['wpm2aws-aws-lightsail-region']);
        exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
    }

    public function runMigration()
    {
        // add_action('admin_post_wpm2aws-run-full-migration', array($this, 'verifyIam'));
        // add_action('admin_post_wpm2aws-run-full-migration', array($this, 'uploadToBucket'));

        // add_action('admin_post_wpm2aws-run-full-migration', array($this, 'exportFileSystemToLightsail'));

        add_action('admin_post_wpm2aws-run-full-migration', array($this, 'runFullMigrationProcess'));
    }

    
    public function runFullMigrationProcess()
    {
        $this->uploadToBucket();
        $this->exportFileSystemToLightsail();

        $this->deleteBucket();
    }



    public function registerSeahorseLicence($licenceDetails)
    {
        if (empty($licenceDetails['wpm2aws-licence-key'])) {
            return false;
        }

        if (empty($licenceDetails['wpm2aws-licence-email'])) {
            return false;
        }
        
        return $register = wpm2aws_register_licence($licenceDetails);
    }




    // DEV TESTING AREA
    // TO BE INTEGRATED ONCE COMPLETE
    public function verifyIamCredentials()
    {
        add_action('admin_post_wpm2aws-dev-testing', array($this, 'verifyIam'));
        add_action('admin_post_wpm2aws-run-full-migration', array($this, 'verifyIam'));
    }

    public function verifyIam()
    {
        $apiGlobal = new WPM2AWS_ApiGlobal();
        $user = $apiGlobal->getIamUser();
        // wp_die("verifyIamCredentials - " . $user);


        return $user;

        wp_die('Verify IAM Credentials Function - Pending');
    }

    public function getAwsRegion()
    {

        add_action('admin_post_wpm2aws-dev-testing', array($this, 'getRegion'));
        add_action('admin_post_wpm2aws-run-full-migration', array($this, 'getRegion'));
    }

    public function getRegion()
    {
        $apiGlobal = new WPM2AWS_ApiGlobal();
        $apiGlobal->getDefaultRegion();

        wp_die('Get AWS Region Function - Pending');
    }

    public function setAwsRegion()
    {
        $apiGlobal = new WPM2AWS_ApiGlobal();
        $apiGlobal->getIamUser();
        add_action('admin_post_wpm2aws-dev-testing', array($this, 'setRegion'));
        add_action('admin_post_wpm2aws-run-full-migration', array($this, 'setRegion'));
    }

    public function setRegion()
    {
        wp_die('Set AWS Region Function - Pending');
    }

    public function getS3Regions()
    {
        add_action('admin_post_wpm2aws-dev-testing', array($this, 'getAllS3Regions'));
    }

    public function getAllS3Regions()
    {
        $apiGlobal = new WPM2AWS_ApiGlobal();
        $apiGlobal->getS3regions();
        wp_die('Get All S3 Regions Function - Pending');
    }

    public function getExistingS3Buckets()
    {
        add_action('admin_post_wpm2aws-dev-testing', array($this, 'getBuckets'));
        add_action('admin_post_wpm2aws_s3_refresh_bucket_list', array($this, 'getBuckets'));
        add_action('admin_post_wpm2aws-run-full-migration', array($this, 'getBuckets'));
    }

    public function getBuckets($subFunction = false)
    {
        $apiGlobal = new WPM2AWS_ApiGlobal();
        $buckets = $apiGlobal->getBucketList();
        if ($buckets === true) {
            if ($subFunction) {
                return true;
            } else {
                set_transient('wpm2aws_admin_success_notice_' . get_current_user_id(), __('Success!<br><br>S3 Bucket List Refreshed', 'migrate-2-aws'));
            }
        } else {
            // wp_die(print_r($buckets));
            set_transient('wpm2aws_admin_warning_notice_' . get_current_user_id(), __('Warning!<br><br>There are currently no S3 Buckets connected with this AWS User<br><br>Please Create a Bucket', 'migrate-2-aws'));
        }
        exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
        return;
        // wp_die('Get Existing S3 Bucket Function - Pending');
    }

    public function createS3Bucket($restricted = true)
    {
        if ($restricted) {
            add_action('admin_post_wpm2aws-dev-testing', array($this, 'createAndUploadBucket'), 10, 1);
            do_action('admin_post_wpm2aws-dev-testing', 'testParam');
        } else {
            add_action('admin_post_wpm2aws-dev-testing', array($this, 'createBucket'));
            add_action('admin_post_wpm2aws_s3_create_bucket', array($this, 'createBucket'));
            add_action('admin_post_wpm2aws-run-full-migration', array($this, 'createBucket'));
        }
    }

    public function createAndUploadBucket($restricted = true)
    {
        $user = $this->verifyIam();

        $bucket = $this->createBucket($restricted);
        if (!$bucket) {
            wp_die('<strong>Bucket was not Created</strong><br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }

        // sns trigger to send mail after S3 bucket created
        try {
            $this->triggerSNSAlert(get_option('wpm2aws-aws-region'), 'S3', $user);
        } catch (Exception $e) {
            wpm2awsLogAction('Error: createAndUploadBucket->triggerSNSAlert: ' . $e->getMessage());
        }

        // print_r($bucket);
        $uploaded = $this->uploadZipToBucket($restricted);
        wp_die('End');
    }


    public function createBucket($restricted = true)
    {
        $apiGlobal = new WPM2AWS_ApiGlobal();
        return $bucket = $apiGlobal->createBucket($restricted);
        // wp_die('Create S3 Bucket Function - Pending');
    }

    public function deleteS3Bucket()
    {
        add_action('admin_post_wpm2aws-dev-testing', array($this, 'deleteBucket'));
        add_action('admin_post_wpm2aws-run-full-migration', array($this, 'deleteBucket'));
    }

    public function deleteBucket()
    {
        $apiGlobal = new WPM2AWS_ApiGlobal();
        $apiGlobal->deleteBucket();
        wp_die('Delete S3 Bucket Function - Pending');
    }

    public function uploadToS3Bucket()
    {
        add_action('admin_post_wpm2aws-dev-testing', array($this, 'uploadToBucket'));
        add_action('admin_post_wpm2aws-run-full-migration', array($this, 'uploadToBucket'));
    }

    public function uploadToBucket($restricted = true)
    {
        $apiGlobal = new WPM2AWS_ApiGlobal();
        $apiGlobal->uploadToBucket($restricted);
        wp_die('Upload To S3 Bucket Function - Pending');
    }
    public function uploadZipToBucket($restricted = true)
    {
        $apiGlobal = new WPM2AWS_ApiGlobal();
        $apiGlobal->uploadZipToBucket($restricted);
        wp_die('Upload Zip To S3 Bucket Function - Pending');
    }

    public function triggerSNSAlert($region, $type, $id, $ip = NULL)
    {
        $apiGlobal = new WPM2AWS_ApiGlobal();
        
        $sourceLocation = get_home_url();
        if (empty($sourceLocation)) {
            $sourceLocation = 'Unknown';
        }

        // $message = 'An ' . $type . ' event via the WPM2AWS software has been triggered by ' . $id . ' in region: ' . $region;
        $message = 'An event has been triggered via the WPM2AWS Software';
        $message .= ' | Event Type : ' . $type;
        $message .= ' | Region : ' . $region;
        $message .= ' | IAM User : ' . $id;
        $message .= ' | Source Location : ' . $sourceLocation;
        
        if($ip != NULL) {
            $message .= ' Instance IP: '.$ip;
        }
        $topic = 'arn:aws:sns:'.$region.':'.WPM2AWS_PLUGIN_AWS_NUMBER.':WPM2AWS_'.$type.'_Created';
        try {
            $sns = $apiGlobal->triggerSNSAlert($message, $topic);
            return $sns;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
            return false;
        }

    }

    public function emptyS3Bucket()
    {
        add_action('admin_post_wpm2aws-dev-testing', array($this, 'emptyBucket'));
        add_action('admin_post_wpm2aws-run-full-migration', array($this, 'emptyBucket'));
    }

    public function emptyBucket()
    {
        $apiGlobal = new WPM2AWS_ApiGlobal();
        $apiGlobal->emptyBucket();
        wp_die('Empty S3 Bucket Function - Pending');
    }

    public function createLightsailInstance()
    {
        add_action('admin_post_wpm2aws-run-full-migration', array($this, 'createLightsail'));

        add_action('admin_post_wpm2aws-dev-testing', array($this, 'createLightsail'));
         // do_action('admin_post_wpm2aws-run-full-migration');
    }

    public function createLightsailInstanceAdmin()
    {
        add_action('admin_post_wpm2aws-run-full-migration-admin', array($this, 'createLightsailAdmin'));
    }


    public function createLightsailInstanceZipped()
    {
        add_action('admin_post_wpm2aws-run-full-migration', array($this, 'createLightsailZipped'));
    }

    /* Latest Version */
    public function createLightsailInstanceZippedRemote()
    {
        add_action('admin_post_wpm2aws-run-full-migration', array($this, 'createLightsailZippedRemote'));
    }

    public function createLightsail()
    {
        $apiGlobal = new WPM2AWS_ApiGlobal();
        $user = $this->verifyIam();
        $instance = $apiGlobal->createLightsail();
        // sns trigger to send mail after AWS Instance created
        $errorMsg = '';
        try {
            $this->triggerSNSAlert(get_option('wpm2aws-aws-lightsail-region'), 'LS', $user, $instance['publicIpAddress']);
        } catch (Exception $e) {
            wpm2awsLogAction('Error: createLightsail->triggerSNSAlert: ' . $e->getMessage());
        }
        $successMsg = 'Success!<br><br>AWS Launched<br><br><a href="http://' . $instance['publicIpAddress'] . '/" target="_blank">' . $instance['publicIpAddress'] . '</a>';

        set_transient('wpm2aws_admin_success_notice_' . get_current_user_id(), __($successMsg, 'migrate-2-aws'));
        exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
        wp_die('Create AWS Instance Function - Pending');
    }

    public function createLightsailAdmin()
    {
        $apiGlobal = new WPM2AWS_ApiGlobal();
        $user = $this->verifyIam();
        $instance = $apiGlobal->createLightsail('admin');

        // sns trigger to send mail after AWS Instance created
        try {
            $this->triggerSNSAlert(get_option('wpm2aws-aws-lightsail-region'), 'LS', $user, $instance['publicIpAddress']);
        } catch (Exception $e) {
            wpm2awsLogAction('Error: createLightsailAdmin->triggerSNSAlert: ' . $e->getMessage());
        }

        set_transient('wpm2aws_admin_success_notice_' . get_current_user_id(), __('Success!<br><br>AWS Launched<br><br><a href="http://' . $instance['publicIpAddress'] . '/" target="_blank">' . $instance['publicIpAddress'] . '</a>', 'migrate-2-aws'));
        exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
        wp_die('Create AWS Instance Function - Pending');
    }

    public function createLightsailZipped()
    {
        $apiGlobal = new WPM2AWS_ApiGlobal();
        $user = $this->verifyIam();
        $instance = $apiGlobal->createLightsail('zipped');

        // sns trigger to send mail after AWS Instance created
        try {
            $this->triggerSNSAlert(get_option('wpm2aws-aws-lightsail-region'), 'LS', $user, $instance['publicIpAddress']);
        } catch (Exception $e) {
            wpm2awsLogAction('Error: createLightsailZipped->triggerSNSAlert: ' . $e->getMessage());
        }
        
        wpm2awsLogRAction('wpm2aws_create_lightsail_success', 'AWS Launched: ' . $instance['publicIpAddress']);
        set_transient('wpm2aws_admin_success_notice_' . get_current_user_id(), __('Success!<br><br>AWS Launched<br><br><a href="http://' . $instance['publicIpAddress'] . '/" target="_blank">' . $instance['publicIpAddress'] . '</a>', 'migrate-2-aws'));
        exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
        wp_die('Create AWS Instance Function - Pending');
    }

    public function createLightsailZippedRemote()
    {
        
        try {
            $apiRemote = new WPM2AWS_ApiRemote();
        } catch (Throwable $e) {
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $e->getMessage());
            exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
        } catch (Exception $e) {
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $e->getMessage());
            exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
        }

        // To Be Moved To Remote
        $user = $this->verifyIam();

        try {
            $instance = $apiRemote->createLightsailFromZip();
        } catch (Throwable $e) {
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $e->getMessage());
            exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
        } catch (Exception $e) {
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $e->getMessage());
            exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
        }
        
        wpm2awsAddUpdateOptions(
            'wpm2aws-lightsail-instance-details',
            array(
                'name' => $instance['name'],
                'region' => $instance['region'],
                'publicIp' => $instance['publicIp'],
                'accessControl' => $instance['accessControl'],
                'details' => $instance['details']
            )
        );

        if (!empty($instance['key-pair-details'])) {
            wpm2awsAddUpdateOptions('wpm2aws_lightsail_ssh', $instance['key-pair-details']);
        }

        $msg =  __('Success!<br><br>Lightsail Launched<br><br><a href="http://' . $instance['publicIp'] . '/" target="_blank">' . $instance['publicIp'] . '</a>', 'migrate-2-aws');
        set_transient('wpm2aws_admin_success_notice_' . get_current_user_id(), $msg);

        exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
    }

    public function exportDatabase()
    {
        add_action('admin_post_wpm2aws-dev-testing', array($this, 'exportDatabaseToLightsail'));
        add_action('admin_post_wpm2aws-run-full-migration', array($this, 'exportDatabaseToLightsail'));
    }

    public function exportDatabaseToLightsail()
    {
        wp_die('Export Database Function - Pending');
    }

    public function exportFileSystem()
    {
        add_action('admin_post_wpm2aws-dev-testing', array($this, 'exportFileSystemToLightsail'));
        add_action('admin_post_wpm2aws-run-full-migration', array($this, 'exportFileSystemToLightsail'));
    }

    public function exportFileSystemToLightsail()
    {
        wp_die('Export Database Function - Pending');
    }

    public function updateDomainName()
    {
        add_action('admin_post_wpm2aws_domainName', array($this, 'addUpdateDomainName'));
    }

    public function addUpdateDomainName()
    {
        $validatePost = wpm2awsValidatePost('domain-name');
        if ($validatePost === false) {
            //ToDo: Return User Error Notice
            wp_die('Invalid Post Data.<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }

        $requiredInputs = array(
            'wpm2aws-aws-lightsail-domain-name' => $_POST['wpm2aws_lightsailDomainName']
        );
        $validatedInputs = wpm2awsValidateSanitizeInputs($requiredInputs);
        if (empty($validatedInputs)) {
            //ToDo: Return User Error Notice

            exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
            wp_die('Invalid Post Data.<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }
        foreach ($validatedInputs as $valInKey => $valInVal) {
            wpm2awsAddUpdateOptions($valInKey, $valInVal);
        }
        exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
    }

    public function resetAll()
    {
        add_action('admin_post_wpm2aws-reset-all-settings', array($this, 'clearAllSavedOptions'));
    }

    public function clearAllSavedOptions($reloadPage = true)
    {

        $optionNames = array(
            'wpm2aws_valid_licence',
            'wpm2aws-licence-key',
            // 'wpm2aws_lightsail_ssh',
            'wpm2aws_licence_key',
            'wpm2aws_licence_email',
            'wpm2aws_licence_url',
            'wpm2aws_valid_licence_type',
            'wpm2aws_valid_licence_plan',
            'wpm2aws_valid_licence_keyp',
            'wpm2aws_valid_licence_keys',
            'wpm2aws_valid_licence_dyck',
            'wpm2aws-customer-type',
            
            'wpm2aws_current_active_step',

            'wpm2aws-iamid',
            'wpm2aws-iampw',
            'wpm2aws-iam-user',

            'wpm2aws-aws-region',

            'wpm2aws-aws-s3-bucket-name',
            'wpm2aws-aws-s3-default-bucket-name',
            'wpm2aws-existingBucketNames',
            'wpm2aws-aws-s3-existing-bucket',

            'wpm2aws-aws-s3-upload-directory-name',
            'wpm2aws-aws-s3-upload-directory-path',

            'wpm2aws-aws-lightsail-name',
            'wpm2aws-aws-lightsail-region',
            'wpm2aws-aws-lightsail-domain-name',

            'wpm2aws_download_db_started',
            'wpm2aws_download_db_complete',

            'wpm2aws_upload_process_start_time',
            'wpm2aws_upload_complete',
            'wpm2aws_upload_started',
            'wpm2aws_upload_failures',
            'wpm2aws_upload_counter',
            'wpm2aws_upload_errors',

            'wpm2aws_admin_upload_complete',
            'wpm2aws_admin_upload_started',
            'wpm2aws_admin_upload_failures',
            'wpm2aws_admin_upload_counter',
            'wpm2aws_admin_upload_errors',

            'wpm2aws_exclude_dirs_from_zip_process',
            'wpm2aws_console_changed_plan_instance_name',
            'wpm2aws_console_changed_plan_instance_ip',
            'wpm2aws_console_copy_snapshot_pending_name',
            'wpm2aws_console_copy_snapshot_pending_region',

            'wpm2aws_fszipper_complete',
            'wpm2aws_fszipper_started',
            'wpm2aws_fszipper_failures',
            'wpm2aws_fszipper_counter',
            'wpm2aws_fszipper_errors',

            'wpm2aws_zipped_fs_upload_complete',
            'wpm2aws_zipped_fs_upload_started',
            'wpm2aws_zipped_fs_upload_failures',
            'wpm2aws_zipped_fs_upload_counter',
            'wpm2aws_zipped_fs_upload_errors',

            'wpm2aws_bgProcessAttempts',

            'wpm2aws-lightsail-instance-details',
        );

        foreach ($optionNames as $option) {
            delete_option($option);
        }

        $pendingUploads = $this->deleteBatchQueue();

        $logs = $this->deleteLogs();


        if ($reloadPage === false) {
            wp_die($pendingUploads . 'Options Cleared<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }
        $notice = 'Notice! The Migration Process has been Reset ' . $pendingUploads;
        set_transient(
            'wpm2aws_admin_error_notice_' . get_current_user_id(),
            __($notice, 'migrate-2-aws')
        );
        exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));

    }

    public function deleteBatchQueue()
    {
        global $wpdb;
        // $existingBatches = $wpdb->get_results( "SELECT option_name FROM {$wpdb->prefix}options WHERE option_name LIKE 'wp_wpm2aws-uploader-all_batch_%'", OBJECT );
        $existingBatches = $wpdb->get_results(
            "SELECT option_name
            FROM {$wpdb->prefix}options
            WHERE option_name
            LIKE 'wp_wpm2aws-uploader-all_batch_%'
            OR
            option_name
            LIKE 'wp_wpm2aws-admin-uploader-all_batch_%'
            OR
            option_name
            LIKE 'wp_wpm2aws-fszipper-all_batch_%'
            OR
            option_name
            LIKE 'wp_wpm2aws-zipped-fs-uploader-all_batch_%'",
            OBJECT
        );
        $result = '';
        if (!empty($existingBatches)) {
            foreach ($existingBatches as $batchIx => $batchDetails) {
                $optionName = $batchDetails->option_name;
                delete_option($optionName);
                // $result .= $batchDetails->option_name;
                // $result .= '<br>';
            }
        }
        // wp_die($result);

            // wp_die(print_r($existingBatches));
        return $notice = 'Pending File Uploads removed.';
    }

    public function deleteLogs()
    {
        wpm2awsLogResetAll();
        wpm2awsZipLogResetAll();
        wpm2awsdownloadZipLogResetAll();
        return true;
    }

    public function clearLightsailDisplayOptions()
    {
        $optionNames = array(
            'wpm2aws-lightsail-instance-details',
        );

        foreach ($optionNames as $option) {
            delete_option($option);
        }
        wp_die('AWS Display Cleared<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');

        // exit(wp_safe_redirect(admin_url('/admin.php?page=wpm2aws')));
    }

    public function exportDb()
    {
        new WPM2AWS_DbDownloader();
    }
}
