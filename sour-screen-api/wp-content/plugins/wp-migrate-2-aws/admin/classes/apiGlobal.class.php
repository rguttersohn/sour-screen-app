<?php

use Aws\Exception\AwsException as ApiException;
use Aws\Iam\IamClient;
use Aws\Iam\IamException;
use Aws\S3\S3Client;
use Aws\S3\Exception as S3Exception;
use Aws\S3\Transfer as S3Transfer;
use Aws\S3\ObjectUploader as S3ObjectUploader;
use Aws\S3\MultipartUploader;
use Aws\Exception\MultipartUploadException;
use Aws\Ec2\Ec2Client;
use Aws\Ec2\Exception as Ec2Exception;
use Aws\Lightsail\LightsailClient;
use Aws\Lightsail\Exception as LightsailException;
use Aws\CloudTrail\CloudTrailClient;
use Aws\CloudTrail\Exception as CloudTrailException;
use Aws\Sns\SnsClient;
use Aws\Exception\AwsException;

class WPM2AWS_ApiGlobal
{
    private $credentials;
    public function __construct()
    {
        $this->loadGlobal();
        
        // Call in the Patch for Vendor DIR
        require_once WPM2AWS_PLUGIN_DIR . '/vendor/aws/aws-sdk-php/src/SeahorsePatch/functions.php';
    }

    private function loadGlobal()
    {
        if (false === get_option('wpm2aws-iamid')) {
            // Convert to Warning Message & Reload Plugin Page
            wp_die("No Access Key ID Saved");
        }

        if (false === get_option('wpm2aws-iampw')) {
            // Convert to Warning Message & Reload Plugin Page
            wp_die("No Secret Access Key Saved");
        }

        $awsRegion = (false === get_option('wpm2aws-aws-region') ? WPM2AWS_PLUGIN_AWS_REGION : get_option('wpm2aws-aws-region'));

        $key = get_option('wpm2aws-iamid');
        $secret = get_option('wpm2aws-iampw');

        if (
            false !==  get_option('wpm2aws_valid_licence_type') &&
            'TRIAL' === strtoupper(get_option('wpm2aws_valid_licence_type'))
        ) {
            $key = str_replace(get_option('wpm2aws_valid_licence_dyck'), '', base64_decode(get_option('wpm2aws-iamid')));
            $secret = str_replace(get_option('wpm2aws_valid_licence_dyck'), '', base64_decode(get_option('wpm2aws-iampw')));
        }

        $this->credentials = array(
            'region' => $awsRegion, /// need to determine the region
            'version' => 'latest',
            'credentials' => array( /// cannot create credentials.ini file so have to use legacy method
                // 'key'    => AWS_ACCESS_KEY_ID,
                // 'secret' => AWS_SECRET_ACCESS_KEY,
                'key' => $key,
                'secret' => $secret,
                )
            );
    }

    // public function getS3regions()
    // {
    //     // try {
    //     //     $regions = new Endpoints(['S3']);
    //     // } catch (ApiException $e) {
    //     //     wp_die("<strong>API Call Failed! (api 1)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
    //     // } catch (EndpointsException $e) {
    //     //     wp_die("<strong>API Call Failed! (AWS Instance Details)</strong><br><br>Error Mgs: " . $e->get() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
    //     // } catch (Exception $e) {
    //     //     wp_die("<strong>API Call Failed! (php 1)</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
    //     // }



    //     try {
    //         $awsSystem = new SystemClient($this->credentials);
    //     } catch (ApiException $e) {
    //         wp_die("<strong>API Call Failed! (api 1)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
    //     } catch (SystemException $e) {
    //         wp_die("<strong>API Call Failed! (AWS Instance Details)</strong><br><br>Error Mgs: " . $e->get() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
    //     } catch (Exception $e) {
    //         wp_die("<strong>API Call Failed! (php 1)</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
    //     }

    //     try {
    //         // $regions = $s3->regions();
    //         $regions = $awsSystem->getParametersByPath(
    //             [
    //                 // 'MaxResults' => <integer>,
    //                 // 'NextToken' => '<string>',
    //                 // 'ParameterFilters' => [
    //                 //     [
    //                 //         'Key' => 'Label', // REQUIRED
    //                 //         // 'Option' => '<string>',
    //                 //           'Values' => ['Value'],
    //                 //     ],
    //                 //     // ...
    //                 // ],
    //                 'Path' => '/aws/service/global-infrastructure/services/s3/regions', // REQUIRED
    //                 // 'Recursive' => true || false,
    //                 // 'WithDecryption' => true || false,
    //             ]
    //         );
    //     } catch (ApiException $e) {
    //         wp_die("<strong>API Call Failed! (api 1)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
    //     } catch (SystemException $e) {
    //         wp_die("<strong>API Call Failed! (AWS Instance Details)</strong><br><br>Error Mgs: " . $e->get() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
    //     } catch (Exception $e) {
    //         wp_die("<strong>API Call Failed! (php 1)</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
    //     }
    //     wp_die(print_r($regions));
    // }

    public function getIamUser()
    {
        try {
            $iam = new IamClient($this->credentials);
            $iamUser = $iam->getUser();
            // return $user = $iamUser;
            // wp_die(print_r($iamUser));
            wpm2awsAddUpdateOptions('wpm2aws-customer-type', 'managed');
            if (strpos($iamUser['User']['Arn'], '8654') === false && strpos($iamUser['User']['Arn'], '7668') === false) {
                wpm2awsAddUpdateOptions('wpm2aws-customer-type', 'self');
            }
            $user = $iamUser['User']['UserName'];
            // wp_die("getIamUser - " . $user);
            return $user;
            // wp_die(print_r($iamUser));
            // wp_die("<strong>Successful API Call!</strong><br><br>Verify IAM User:<br>IAM User Name: " . $iamUser['User']['UserName'] . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (IamException $e) {
            set_transient(
                'wpm2aws_admin_error_notice_' . get_current_user_id(),
                __('Error!<br><br>' . $e->getAwsErrorMessage() . '<br><br>Please Try Again', 'migrate-2-aws')
            );
            wpm2awsLogRAction('getIamUser Fail', 'API Call Failed (iam): ' . $e->getMessage());
            exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
            // wp_die("<strong>API Call Failed! (IAM)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (ApiException $e) {
            set_transient(
                'wpm2aws_admin_error_notice_' . get_current_user_id(),
                __('Error!<br><br>' . $e->getAwsErrorMessage() . '<br><br>Please Try Again', 'migrate-2-aws')
            );
            wpm2awsLogRAction('getIamUser Fail', 'API Call Failed (api): ' . $e->getMessage());
            exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
            // wp_die("<strong>API Call Failed! (api)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Exception $e) {
            set_transient(
                'wpm2aws_admin_error_notice_' . get_current_user_id(),
                __('Error!<br><br>' . $e->getMessage() . '<br><br>Please Try Again', 'migrate-2-aws')
            );
            wpm2awsLogRAction('getIamUser Fail', 'API Call Failed (php): ' . $e->getMessage());
            exit(wp_redirect(admin_url('/admin.php?page=wpm2aws')));
            // wp_die("<strong>API Call Failed!</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }
    }

    public function getIamClient()
    {
        try {
            $iam = new IamClient($this->credentials);
            return $iam;
        } catch (IamException $e) {
            wp_die("<strong>API Call Failed! (IAM)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (ApiException $e) {
            wp_die("<strong>API Call Failed! (api)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Exception $e) {
            wp_die("<strong>API Call Failed!</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }
    }

    public function getEc2Client()
    {
        try {
            $ec2 = new Ec2Client($this->credentials);
            return $ec2;
        } catch (Ec2Exception $e) {
            wp_die("<strong>API Call Failed! (Ec2)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (ApiException $e) {
            wp_die("<strong>API Call Failed! (api)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Exception $e) {
            wp_die("<strong>API Call Failed!</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }
    }

    public function getDefaultRegion()
    {
        try {
            $client = new IamClient($this->credentials);
            $region = $client->getRegion();
            wp_die("<strong>Successful API Call!</strong><br><br>Get IAM User's Region:<br>Region: " . $region . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (ApiException $e) {
            wp_die("<strong>API Call Failed! (api)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Exception $e) {
            wp_die("<strong>API Call Failed!</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }
    }

    private function getIamInstanceProfile($iamClient)
    {
        try {
            $instanceProfile = $iamClient->getInstanceProfile([
                'InstanceProfileName' => 'migrateInstanceProfile'
            ]);
            return $instanceProfile;
        } catch (IamException $e) {
            wp_die("<strong>API Call Failed! (IAM - Get Instance PRofile)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (ApiException $e) {
            wp_die("<strong>API Call Failed! (api - Get Instance PRofile)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Exception $e) {
            wp_die("<strong>API Call Failed!</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }
    }

    public function attachRoleToLightsail($instanceID)
    {
        $iamClient = $this->getIamClient();

        try {
            $instanceProfile = $iamClient->createInstanceProfile([
                'InstanceProfileName' => 'migrateInstanceProfile',
                // 'Path' => 'S3Access',
            ]);
            // [
            //     'InstanceProfile' => [
            //         'Arn' => '<string>',
            //         'CreateDate' => <DateTime>,
            //         'InstanceProfileId' => '<string>',
            //         'InstanceProfileName' => '<string>',
            //         'Path' => '<string>',
            //         'Roles' => [
            //             [
            //                 'Arn' => '<string>',
            //                 'AssumeRolePolicyDocument' => '<string>',
            //                 'CreateDate' => <DateTime>,
            //                 'Description' => '<string>',
            //                 'MaxSessionDuration' => <integer>,
            //                 'Path' => '<string>',
            //                 'PermissionsBoundary' => [
            //                     'PermissionsBoundaryArn' => '<string>',
            //                     'PermissionsBoundaryType' => 'PermissionsBoundaryPolicy',
            //                 ],
            //                 'RoleId' => '<string>',
            //                 'RoleName' => '<string>',
            //                 'Tags' => [
            //                     [
            //                         'Key' => '<string>',
            //                         'Value' => '<string>',
            //                     ],
            //                     // ...
            //                 ],
            //             ],
            //             // ...
            //         ],
            //     ],
            // ]
            // return $attachRole;
        } catch (IamException $e) {
            if ($e->getAwsErrorCode() !== 'EntityAlreadyExists') {
                wp_die("<strong>API Call Failed! (IAM - Create Instance Profile)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
            } else {
                $instanceProfile = $this->getIamInstanceProfile($iamClient);
            }
        } catch (ApiException $e) {
            if ($e->getAwsErrorCode() !== 'EntityAlreadyExists') {
                wp_die("<strong>API Call Failed! (api - Create Instance Profile)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
            } else {
                $instanceProfile = $this->getIamInstanceProfile($iamClient);
            }
        } catch (Exception $e) {
            wp_die("<strong>API Call Failed!</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }


        try {
            $attachRole = $iamClient->addRoleToInstanceProfile([
                'InstanceProfileName' => 'migrateInstanceProfile',
                'RoleName' => 'MigrationPluginRole',
            ]);
            // return $attachRole;
        } catch (IamException $e) {
            if ($e->getAwsErrorCode() !== 'LimitExceeded') {
                wp_die("<strong>API Call Failed! (IAM - Attach Role)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
            }
        } catch (ApiException $e) {
            if ($e->getAwsErrorCode() !== 'LimitExceeded') {
                wp_die("<strong>API Call Failed! (api - Attach Role)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
            }
        } catch (Exception $e) {
            wp_die("<strong>API Call Failed!</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }

        $ec2Client = $this->getEc2Client();

        $associated = $this->associateIamRole($ec2Client, $instanceProfile, $instanceID);
    }

    // Ec2 Functions
    public function associateIamRole($ec2Client, $instanceProfile, $instanceID)
    {
        try {
            $associated = $ec2Client->associateIamInstanceProfile([
                'IamInstanceProfile' => [ // REQUIRED
                    // 'Arn' => $instanceProfile['InstanceProfile']['Arn'],
                    'Name' => $instanceProfile['InstanceProfile']['InstanceProfileName'],
                ],
                'InstanceId' => $instanceID, // REQUIRED
            ]);
            // wp_die(print_r($associated));
            return $associated;
        } catch (Ec2Exception $e) {
            wp_die("<strong>API Call Failed! (Ec2 - Associate IAM)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (ApiException $e) {
            wp_die("<strong>API Call Failed! (api - Associate IAM)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Exception $e) {
            wp_die("<strong>API Call Failed!</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }
    }


    private function describeInstanceDetails()
    {
        $ec2Client = $this->getEc2Client();

        try {
            $instanceDetails = $ec2Client->describeInstances([
                // 'DryRun' => true || false,
                'Filters' => [
                    [
                        'Name' => 'tag-key',
                        'Values' => ['create-origin'],
                    ],
                    // ...
                ],
                // 'InstanceIds' => ['<string>', ...],
                // 'MaxResults' => <integer>,
                // 'NextToken' => '<string>',
            ]);

            // wp_die(print_r($instanceDetails));
            return $instanceDetails;
        } catch (Ec2Exception $e) {
            wp_die("<strong>API Call Failed! (describeInstanceDetails - Ec2 Details)</strong><br><br>Error Mgs: " . $e->get() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (ApiException $e) {
            wp_die("<strong>API Call Failed! (describeInstanceDetails - api 1)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Exception $e) {
            wp_die("<strong>API Call Failed! (describeInstanceDetails - php 1)</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }
    }






    // S3 Functions
    public function getBucketList()
    {
        try {
            $client = new S3Client($this->credentials);
            $buckets = $client->listBuckets();
            $bucketNames = array();
            if (!empty($buckets['Buckets'])) {
                foreach ($buckets['Buckets'] as $bucketItems) {
                    $bucketNames[$bucketItems['Name']] =  $bucketItems['Name'];
                }
                wpm2awsAddUpdateOptions('wpm2aws-existingBucketNames', $bucketNames);
            }
            return true;
            // wp_die("<strong>Successful API Call!</strong><br><br>Get Existing Buckets.<br><br>Bucket List:<br>" . implode('<br>', array_values($bucketNames)) . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (ApiException $e) {
            return "<strong>API Call Failed! (api)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage();
            // wp_die("<strong>API Call Failed! (api)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Exception $e) {
            return "<strong>API Call Failed!</strong><br><br>Error Mgs: " . $e->getMessage();
            // wp_die("<strong>API Call Failed!</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }
    }

    public function createBucket($restricted = true)
    {
        // - Should not contain uppercase characters
        // - Should not contain underscores (_)
        // - Should be between 3 and 63 characters long
        // - Should not end with a dash
        // - Cannot contain two, adjacent periods
        // - Cannot contain dashes next to periods (e.g., "my-.bucket.com" and "my.-bucket" are invalid)
        // if (false === get_option('wpm2aws-aws-s3-bucket-name')) {
        //     return 'Error! You must give your S3 Bucket a name before it can be created.<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>';
        // }
        try {
            $client = new S3Client($this->credentials);
        } catch (ApiException $e) {
            wp_die("<strong>API Call Failed! (api)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (S3Exception $e) {
            wp_die("<strong>S3 Call Failed! (api)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Throwable $e) {
            // wp_die(print_r($e->getCode()));
            wp_die("<strong>Throwable Failed!</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Exception $e) {
            wp_die("<strong>API Call Failed!</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }

        $bucketExists = $this->checkBucketExists($client, $restricted);

        if (!$bucketExists) {
            $newBucket = $this->createNewBucket($client, $restricted);
            return $newBucket;
        } else {
            if (get_option('wpm2aws-customer-type') === 'managed') {
                return true;
            }
            wp_die('<strong>Bucket Already Exists</strong><br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }
    }

    public function getBucketResidesLocation($restricted = true)
    {
        $bucketName = ($restricted ? get_option('wpm2aws-aws-s3-default-bucket-name') : get_option('wpm2aws-aws-s3-bucket-name'));

        try {
            $client = new S3Client($this->credentials);
        } catch (ApiException $e) {
            $errorMsg = $e->getAwsErrorMessage();
            if (empty($errorMsg)) {
                $errorMsg = $e->getMessage();
            }
            wpm2awsLogAction("API Call Failed! (getBucketResidesLocation - api 1). Error Mgs: " . $errorMsg);
            throw new Exception($errorMsg);
            return false;
        } catch (S3Exception $e) {
            $errorMsg = $e->getAwsErrorMessage();
            if (empty($errorMsg)) {
                $errorMsg = $e->getMessage();
            }
            wpm2awsLogAction("API Call Failed! (getBucketResidesLocation - s3 1). Error Mgs: " . $errorMsg);
            throw new Exception($errorMsg);
            return false;
        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
            if (empty($errorMsg)) {
                $errorMsg = $e->getMessage();
            }
            wpm2awsLogAction("API Call Failed! (getBucketResidesLocation - php 1). Error Mgs: " . $errorMsg);
            throw new Exception($errorMsg);
            return false;
        }


        try {
            $location = $client->getBucketLocation([
                'Bucket' => $bucketName, //'<string>', // REQUIRED
            ]);
            return $location->get('LocationConstraint');
        } catch (ApiException $e) {
            $errorMsg = $e->getAwsErrorMessage();
            if (empty($errorMsg)) {
                $errorMsg = $e->getMessage();
            }
            wpm2awsLogAction("API Call Failed! (getBucketResidesLocation - api 2). Error Mgs: " . $errorMsg);
            throw new Exception($errorMsg);
            return false;
        } catch (S3Exception $e) {
            $errorMsg = $e->getAwsErrorMessage();
            if (empty($errorMsg)) {
                $errorMsg = $e->getMessage();
            }
            wpm2awsLogAction("API Call Failed! (getBucketResidesLocation - s3 2). Error Mgs: " . $errorMsg);
            throw new Exception($errorMsg);
            return false;
        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
            if (empty($errorMsg)) {
                $errorMsg = $e->getMessage();
            }
            wpm2awsLogAction("API Call Failed! (getBucketResidesLocation - php 2). Error Mgs: " . $errorMsg);
            throw new Exception($errorMsg);
            return false;
        }
    }

    
    public function getSelectedBucketPolicy($restricted = true)
    {
        $bucketName = ($restricted ? get_option('wpm2aws-aws-s3-default-bucket-name') : get_option('wpm2aws-aws-s3-bucket-name'));
        // return 'testing api';
        try {
            $client = new S3Client($this->credentials);
        } catch (ApiException $e) {
            $errorMsg = $e->getAwsErrorMessage();
            if (empty($errorMsg)) {
                $errorMsg = $e->getMessage();
            }
            wpm2awsLogAction("API Call Failed! (getSelectedBucketPolicy - api 1). Error Mgs: " . $errorMsg);
            throw new Exception($errorMsg);
            return false;
        } catch (S3Exception $e) {
            $errorMsg = $e->getAwsErrorMessage();
            if (empty($errorMsg)) {
                $errorMsg = $e->getMessage();
            }
            wpm2awsLogAction("API Call Failed! (getSelectedBucketPolicy - s3 1). Error Mgs: " . $errorMsg);
            throw new Exception($errorMsg);
            return false;
        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
            if (empty($errorMsg)) {
                $errorMsg = $e->getMessage();
            }
            wpm2awsLogAction("API Call Failed! (getSelectedBucketPolicy - php 1). Error Mgs: " . $errorMsg);
            throw new Exception($errorMsg);
            return false;
        }

        // return 'testing api';

        try {
            $policy = $client->getBucketPolicy([
                'Bucket' => $bucketName, //'<string>', // REQUIRED
            ]);
            // wp_die($policy->get('Policy'));
            return $policy->get('Policy');
        } catch (ApiException $e) {
            $errorMsg = $e->getAwsErrorMessage();
            if (empty($errorMsg)) {
                $errorMsg = $e->getMessage();
            }
            wpm2awsLogAction("API Call Failed! (getSelectedBucketPolicy - api 2). Error Mgs: " . $errorMsg);
            throw new Exception($errorMsg);
            return false;
        } catch (S3Exception $e) {
            $errorMsg = $e->getAwsErrorMessage();
            if (empty($errorMsg)) {
                $errorMsg = $e->getMessage();
            }
            wpm2awsLogAction("API Call Failed! (getSelectedBucketPolicy - s3 2). Error Mgs: " . $errorMsg);
            throw new Exception($errorMsg);
            return false;
        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
            if (empty($errorMsg)) {
                $errorMsg = $e->getMessage();
            }
            wpm2awsLogAction("API Call Failed! (getSelectedBucketPolicy - php 2). Error Mgs: " . $errorMsg);
            throw new Exception($errorMsg);
            return false;
        }
    }

    

    private function createNewBucket($client, $restricted = true)
    {
        // $bucketName = ($restricted ? get_option('wpm2aws-aws-s3-default-bucket-name') . 'x' : get_option('wpm2aws-aws-s3-bucket-name'));
        $bucketName = ($restricted ? get_option('wpm2aws-aws-s3-default-bucket-name') : get_option('wpm2aws-aws-s3-bucket-name'));

        try {
            $bucket = $client->createBucket(
                [
                    'Bucket' => $bucketName,
                    'CreateBucketConfiguration' => [
                        'LocationConstraint' => $this->credentials['region'],
                    ],
                ]
            );
            return $bucket;
            wp_die("<strong>Successful API Call!</strong><br><br>Create Bucket.<br><br>Bucket Name:<br>" . $bucket . '<br>Bucket Region:<br>' . $this->credentials['region'] . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (ApiException $e) {
            if (get_option('wpm2aws-customer-type') === 'managed') {
                if ($e->getAwsErrorCode() === 'BucketAlreadyOwnedByYou') {
                    return $bucketName;
                }
            }
            wpm2awsLogRAction('wpm2aws_create_bucket_fail', 'API Call Failed (api): ' . $e->getAwsErrorMessage() . ' | Bucket Name: ' . $bucketName);
            wp_die("<strong>API Call Failed! (createNewBucket - api 1)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (S3Exception $e) {
            wpm2awsLogRAction('wpm2aws_create_bucket_fail', 'API Call Failed (s3): ' . $e->getMessage());
            wp_die("<strong>API Call Failed! (createNewBucket - S3 1)</strong><br><br>Error Mgs: " . $e->get() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Exception $e) {
            wpm2awsLogRAction('wpm2aws_create_bucket_fail', 'API Call Failed (php): ' . $e->getMessage());
            wp_die("<strong>API Call Failed! (createNewBucket - php 1)</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }
    }


    private function checkBucketExists($client, $restricted = true)
    {
        // $bucketName = ($restricted ? get_option('wpm2aws-aws-s3-default-bucket-name') . 'x' : get_option('wpm2aws-aws-s3-bucket-name'));
        $bucketName = ($restricted ? get_option('wpm2aws-aws-s3-default-bucket-name') : get_option('wpm2aws-aws-s3-bucket-name'));

        try {
            $exists = $client->doesBucketExist($bucketName);
            return $exists;
        } catch (ApiException $e) {
            wp_die("<strong>API Call Failed! (checkBucketExists - checkBucketExists)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (S3Exception $e) {
            wp_die("<strong>API Call Failed! (s3 2 - checkBucketExists)</strong><br><br>Error Mgs: " . $e->get() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Exception $e) {
            wp_die("<strong>API Call Failed! (php 2 - checkBucketExists)</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }
    }

    public function deleteBucket()
    {
        if (false === get_option('wpm2aws-aws-s3-bucket-name')) {
            exit('Error! You must give your S3 Bucket a name before it can be created.<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }
        try {
            $client = new S3Client($this->credentials);
        } catch (ApiException $e) {
            wp_die("<strong>API Call Failed! (api)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Exception $e) {
            wp_die("<strong>API Call Failed!</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }

        $bucketExists = $this->checkBucketExists($client);

        if ($bucketExists) {
            $this->deleteWpm2awsBucket($client);
        } else {
            wp_die('<strong>Delete Bucket - Bucket Does not Exist (1) </strong><br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }
    }

    private function deleteWpm2awsBucket($client)
    {
        try {
            $deleted = $client->deleteBucket(
                [
                    'Bucket' => get_option('wpm2aws-aws-s3-bucket-name'),
                ]
            );
            if (defined('WPM2AWS_TESTING') || defined('WPM2AWS_DEBUG') || defined('WPM2AWS_DEV')) {
                wpm2awsLogAction("<strong>Successful API Call!</strong><br><br>Delete Bucket.<br><br>Bucket Name:<br>" . get_option('wpm2aws-aws-s3-bucket-name') . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
            }
            return $deleted;
        } catch (ApiException $e) {
            if (defined('WPM2AWS_TESTING') || defined('WPM2AWS_DEBUG') || defined('WPM2AWS_DEV')) {
                wpm2awsLogAction("<strong>API Call Failed! (api)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
            }
            return false;
        } catch (S3Exception $e) {
            if (defined('WPM2AWS_TESTING') || defined('WPM2AWS_DEBUG') || defined('WPM2AWS_DEV')) {
                wpm2awsLogAction("<strong>API Call Failed! (S3)</strong><br><br>Error Mgs: " . $e->get() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
            }
            return false;
        } catch (Exception $e) {
            if (defined('WPM2AWS_TESTING') || defined('WPM2AWS_DEBUG') || defined('WPM2AWS_DEV')) {
                wpm2awsLogAction("<strong>API Call Failed! (php)</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
            }
            return false;
        }
    }

    public function uploadSingleZipToBucket($basePath, $filePath, $fileName, $restricted = true)
    {
        $pathSeparator = '/';
        if (strpos($basePath, '\\') !== false) {
            $pathSeparator = '\\';
        }

        $fullPath = $basePath . $pathSeparator . $filePath;

        if (defined('WPM2AWS_TESTING') || defined('WPM2AWS_DEBUG') || defined('WPM2AWS_DEV')) {
            wpm2awsLogAction($fullPath);
        }

        try {
            $client = new S3Client($this->credentials);
        } catch (ApiException $e) {
            wpm2awsLogAction("<strong>API Call Failed! (uploadToBucket - api 1)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Exception $e) {
            wpm2awsLogAction("<strong>API Call Failed! (uploadToBucket - php 1)</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }

        // $zipped = $this->gZipFile($fullPath);
        $directoryName = 'wp-content';
        // $directoryName = strrchr($filePath, 'wp-content') . '/' . $directoryContent;
        // if ($zipped) {
        // $this->putGzipObjectToWp2AwsBucket($client, $zipped, 'wp-content/' . $filePath, $restricted);
        $source = $fullPath . $fileName;
        $destination = 'wp-content/' . $fileName;

        $transfer = $this->transferToWp2AwsBucket($client, $source, $destination);

        if (defined('WPM2AWS_TESTING') || defined('WPM2AWS_DEBUG') || defined('WPM2AWS_DEV')) {
            wpm2awsLogAction("Debug: Transfer: " . $transfer);
        }

        return $transfer;
        // }
    }

    public function zipDirectoryAndUpload($basePath, $fileName)
    {
        $pathSeparator = '/';
        if (strpos($basePath, '\\') !== false) {
            $pathSeparator = '\\';
        }

        $pathSeparator = DIRECTORY_SEPARATOR;

        $dirPath = $basePath . $pathSeparator . $fileName;
        $zippedFilePath = '';
        try {
            $zippedFilePath = $this->zipFullDirectory($basePath . $pathSeparator, $fileName, $pathSeparator);
            if (defined('WPM2AWS_TESTING') || defined('WPM2AWS_DEBUG') || defined('WPM2AWS_DEV')) {
                wpm2awsLogAction('Full Zip PAth: ' . $zippedFilePath);
            }
        } catch (Exception $e) {
            wpm2awsLogAction('Full Zip Error: ' . $e->getMessage());
            return false;
        }

        if ('' === $zippedFilePath) {
            wpm2awsLogAction('Full Zip Error - Incomplete');
            return $status = '404';
            // return false;
        }

        $restricted = true;
        if (false !== get_option('wpm2aws-customer-type') && 'self' === get_option('wpm2aws-customer-type')) {
            $restricted = false;
        }

        try {
            $transferred = $this->uploadZippedFileToBucket($zippedFilePath . '.zip', $restricted);
            if (defined('WPM2AWS_TESTING') || defined('WPM2AWS_DEBUG') || defined('WPM2AWS_DEV')) {
                wpm2awsLogAction('Full Zip: ' . $transferred);
            }
            return $transferred;
        } catch (Exception $e) {
            wpm2awsLogAction('Full Zip Error: ' . $e->getMessage());
            return false;
        }
    }

    private function zipFullDirectory($dirPath, $directoryName, $pathSeparator, $forDownload = false)
    {
        if (defined('WPM2AWS_TESTING') || defined('WPM2AWS_DEBUG') || defined('WPM2AWS_DEV')) {
            wpm2awsLogAction('Zipping Directory @ : ' . $dirPath . $directoryName);
        }

        if (defined('WPM2AWS_TESTING') || defined('WPM2AWS_DEBUG') || defined('WPM2AWS_DEV')) {
            wpm2awsLogAction('Zipping Directory @ : ' . $dirPath . $directoryName);
        }
        
        $zipFilePath = wpm2aws_compress_directory($dirPath, $directoryName, $pathSeparator, $forDownload);
        return $zipFilePath;

        // // Enter the name to creating zipped directory
        // $zipcreated = "TestZip.zip";

        // // Create new zip class
        // $zip = new ZipArchive;

        // if ($zip->open($zipcreated, ZipArchive::CREATE) === true) {
        //     // Store the path into the variable
        //     $dir = opendir($dirPath);

        //     while ($file = readdir($dir)) {
        //         if (is_file($dirPath.$file)) {
        //             $zip -> addFile($dirPath.$file, $file);
        //         }
        //     }
        //     $zip ->close();
        // }

        // return $zip;
    }

    public function backgroundTransferFullDirToS3($basePath, $filePath = '', $restricted = true)
    {
        $fullPath = $basePath . '/' . $filePath;
        // $fullPath = $basePath;
        if (defined('WPM2AWS_TESTING') || defined('WPM2AWS_DEBUG') || defined('WPM2AWS_DEV')) {
            wpm2awsLogAction($fullPath);
        }

        try {
            $client = new S3Client($this->credentials);
        } catch (ApiException $e) {
            wpm2awsLogAction('API Call Failed! (uploadToBucket - api 1): Error Mgs: ' . $e->getAwsErrorCode() . '. ' . $e->getAwsErrorMessage());
            // wp_die("<strong>API Call Failed! (uploadToBucket - api 1)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
            return $status = '404';
            // return false;
        } catch (Exception $e) {
            wpm2awsLogAction('API Call Failed! (uploadToBucket - php 1):  Error Mgs: ' . $e->getMessage());
            // wp_die("<strong>API Call Failed! (uploadToBucket - php 1)</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
            return $status = '404';
            // return false;
        }

        $bucketExists = $this->checkBucketExists($client, $restricted);

        if ($bucketExists) {
            $source = $fullPath;
            $destination = 'wp-content/' . $filePath;
            if (defined('WPM2AWS_TESTING') || defined('WPM2AWS_DEBUG') || defined('WPM2AWS_DEV')) {
                wpm2awsLogAction('Debug: Source => ' . $source);
                wpm2awsLogAction('Debug: Destination => ' . $destination);
            }
            $transfer = $this->transferToWp2AwsBucket($client, $source, $destination, $restricted);
            return $transfer;
        } else {
            wpm2awsLogAction('Upload Bucket - Bucket Does not Exist (2): ' . get_option('wpm2aws-aws-s3-bucket-name'));
            // wp_die('<strong>Upload Bucket - Bucket Does not Exists: ' . get_option('wpm2aws-aws-s3-bucket-name') . '</strong><br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
            return $status = '404';
            // return false;
        }
    }



    public function uploadToBucket($restricted = true)
    {
        // Check Bucket Name Available
        if ($restricted && false === get_option('wpm2aws-aws-s3-default-bucket-name')) {
            wp_die('Error! S3 Bucket cannot be created at this time. Ref: utb_1_no_bucket_name<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }
        if (!$restricted && false === get_option('wpm2aws-aws-s3-bucket-name')) {
            wp_die('Error! You must give your S3 Bucket a name before it can be created.<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }

        try {
            // Check File Exists
            $fullFilePath = get_option('wpm2aws-aws-s3-upload-directory-path') . '/' . get_option('wpm2aws-aws-s3-upload-directory-name');
            if (!is_dir($fullFilePath)) {
                throw new Exception('Error! Directory does not exist:<br><br>' . $fullFilePath);
            }

            // $uploadContents = fopen(
            //     $fullFilePath,
            //     'r'
            // );
            // if (!$uploadContents) {
            //     throw new Exception('Error! File open failed.:<br><br>' . $fullFilePath);
            // }
        } catch (Exception $e) {
            wp_die("<strong>Directory Upload Failed!</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }

        try {
            $client = new S3Client($this->credentials);
        } catch (ApiException $e) {
            wp_die("<strong>API Call Failed! (uploadToBucket - api 1)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Exception $e) {
            wp_die("<strong>API Call Failed! (uploadToBucket - php 1)</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }

        $bucketExists = $this->checkBucketExists($client, $restricted);

        if ($bucketExists) {
            $fileList = $this->listUploadContentItems($fullFilePath);

            $this->zipAndUploadFiles($client, $fileList, $fullFilePath, $restricted);

        // exit(print_r($fileList));
            // zip the directory
            // $uploadFile = wpm2aws_compress_directory(get_option('wpm2aws-aws-s3-upload-directory-path') . '/' . get_option('wpm2aws-aws-s3-upload-directory-name'));
            // wp_die('Zip Created');
            // $this->uploadToWp2AwsBucket($client, $uploadContents);
            // $this->transferToWp2AwsBucket($client, $restricted);
        } else {
            wp_die('<strong>Upload Bucket - Bucket Does not Exist (3) : ' . get_option('wpm2aws-aws-s3-bucket-name') . '</strong><br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }
    }

    public function uploadZipToBucket($restricted = true)
    {
        // Check Bucket Name Available
        if ($restricted && false === get_option('wpm2aws-aws-s3-default-bucket-name')) {
            wp_die('Error! S3 Bucket cannot be created at this time. Ref: utb_1_no_bucket_name<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }
        if (!$restricted && false === get_option('wpm2aws-aws-s3-bucket-name')) {
            wp_die('Error! You must give your S3 Bucket a name before it can be created.<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }

        try {
            // Check File Exists
            $fullZipFilePath = get_option('wpm2aws-aws-s3-upload-directory-path') . '/' . get_option('wpm2aws-aws-s3-upload-directory-name') . '.zip';
            if (!file_exists($fullZipFilePath)) {
                throw new Exception('Error! Zip File does not exist:<br><br>' . $fullZipFilePath);
            }
        } catch (Exception $e) {
            wp_die("<strong>Directory Upload Failed!</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }


        try {
            $client = new S3Client($this->credentials);
        } catch (ApiException $e) {
            wp_die("<strong>API Call Failed! (uploadZipToBucket - api 1)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Exception $e) {
            wp_die("<strong>API Call Failed! (uploadZipToBucket - php 1)</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }

        $bucketExists = $this->checkBucketExists($client, $restricted);

        if ($bucketExists) {
            // zip the directory
            // $uploadFile = wpm2aws_compress_directory(get_option('wpm2aws-aws-s3-upload-directory-path') . '/' . get_option('wpm2aws-aws-s3-upload-directory-name'));
            // wp_die('Zip Created');
            // $this->uploadToWp2AwsBucket($client, $uploadContents);
            // $this->transferToWp2AwsBucket($client, $restricted);
            $this->uploadZipToWp2AwsBucket($client, $fullZipFilePath, $restricted);
        } else {
            wp_die('<strong>Upload Bucket - Bucket Does not Exist (4) : ' . get_option('wpm2aws-aws-s3-bucket-name') . '</strong><br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }
    }

    private function uploadZippedFileToBucket($fullZipFilePath, $restricted = true)
    {
        // Check Bucket Name Available
        if ($restricted && false === get_option('wpm2aws-aws-s3-default-bucket-name')) {
            wpm2awsLogAction('Error! S3 Bucket cannot be created at this time. Ref: utb_1_no_bucket_name');
            return false;
        }
        if (!$restricted && false === get_option('wpm2aws-aws-s3-bucket-name')) {
            wpm2awsLogAction('Error! You must give your S3 Bucket a name before it can be created)');
            return false;
        }

        try {
            // Check File Exists
            if (!file_exists($fullZipFilePath)) {
                throw new Exception('Error! Zip File does not exist: ' . $fullZipFilePath);
                return false;
            }
        } catch (Exception $e) {
            wpm2awsLogAction("Directory Upload Failed! Error Mgs: " . $e->getMessage());
            return false;
        }


        try {
            $client = new S3Client($this->credentials);
        } catch (ApiException $e) {
            wpm2awsLogAction("API Call Failed! (uploadZipToBucket - api 1) - Error Mgs: " . $e->getAwsErrorCode() . ' - ' . $e->getAwsErrorMessage());
            return false;
        } catch (Exception $e) {
            wpm2awsLogAction("API Call Failed! (uploadZipToBucket - php 1)</strong><br><br>Error Mgs: " . $e->getMessage());
            return false;
        }

        $bucketExists = $this->checkBucketExists($client, $restricted);

        if ($bucketExists) {
            // zip the directory
            // $uploadFile = wpm2aws_compress_directory(get_option('wpm2aws-aws-s3-upload-directory-path') . '/' . get_option('wpm2aws-aws-s3-upload-directory-name'));
            // wp_die('Zip Created');
            // $this->uploadToWp2AwsBucket($client, $uploadContents);
            // $this->transferToWp2AwsBucket($client, $restricted);
            $uploaded = $this->uploadZipToWp2AwsBucket($client, $fullZipFilePath, $restricted);
            return $uploaded;
        } else {
            wpm2awsLogAction('Upload Bucket - Bucket Does not Exist (4) : ' . get_option('wpm2aws-aws-s3-bucket-name'));
            return false;
        }
    }

    public function triggerSNSAlert($message, $topic)
    {
        try {
            $this->credentials['version'] = '2010-03-31';
            $SnSclient = new SnsClient($this->credentials);
            $result = $SnSclient->publish([
                'Message' => $message,
                'TopicArn' => $topic,
            ]);
        } catch (ApiException $e) {
            throw new Exception('API Call Failed! triggerSNSAlert (api) Error Mgs: ' . $e->getAwsErrorCode() . ' - ' . $e->getAwsErrorMessage());
            //            wp_die("<strong>API Call Failed! (api)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (AwsException $e) {
            throw new Exception('API Call Failed! triggerSNSAlert (aws) Error Mgs: ' . $e->getMessage());
            // wp_die("<strong>API Call Failed!</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Exception $e) {
            throw new Exception('API Call Failed! triggerSNSAlert (php) Error Mgs: ' . $e->getMessage());
        }
    }


    private function listUploadContentItems($parentDirectoryName)
    {
        $fullListing = array();

        $fullListing = $this->listUploadContentSubItems($parentDirectoryName);
        $directories = $fullListing['dir'];
        unset($fullListing['dir']);

        while (!empty($directories)) {
            foreach ($directories as $dirIx => $dirName) {
                $subItems = $this->listUploadContentSubItems($dirName);
                $subDirs = $subItems['dir'];
                unset($subItems['dir']);
                unset($directories[$dirIx]);
                // $fullListing = array_merge($fullListing[$parentDirectoryName], $subItems);
                // array_push($fullListing, $subItems);
                $fullListing[$parentDirectoryName][$dirName] = $subItems[$dirName];
                // foreach ($subItems as $subDir => $subVals) {
                //     $fullListing[$subDir] = $subVals;
                // }
                if (!empty($subDirs)) {
                    $directories = array_merge($directories, $subDirs);
                }
            }
        }
        // wp_die(print_r($directories));
        return $fullListing;
    }

    private function listUploadContentSubItems($directoryPath)
    {
        $listing = array();

        // Get a list of items in Sub Directory
        $items = scandir($directoryPath);
        // Remove the Linux path prefixes
        $items = array_diff($items, array('..', '.'));
        $items = array_merge($items);

        // Get a list of any sub Directories
        $directories = array();
        foreach ($items as $itemIx => $itemVal) {
            if (is_dir($directoryPath . '/' . $itemVal)) {
                array_push($directories, $directoryPath . '/' . $itemVal);
                unset($items[$itemIx]);
            }
        }
        $listing[$directoryPath] = $items;
        $listing['dir'] = $directories;

        // wp_die(print_r($listing));
        return $listing;
    }

    private function zipAndUploadFiles($client, $fileList, $baseDir, $restricted)
    {
        $missingFiles = array();
        $directoryNameList = array();
        foreach ($fileList as $directoryPath => $directoryContent) {
            if (is_array($directoryContent)) {
                $this->zipAndUploadFiles($client, $directoryContent, $directoryPath, $restricted);
            } else {
                $directoryName = strrchr($baseDir, 'wp-content') . '/' . $directoryContent;
                // array_push($directoryNameList, $directoryName);
                // if (!file_exists($baseDir . '/' . $directoryContent)) {
                //     wp_die($baseDir . '/' . $directoryContent);
                //     array_push($missingFiles, $baseDir . '/' . $directoryContent);
                // }
                $zipped = $this->gZipFile($baseDir . '/' . $directoryContent);
                $this->putGzipObjectToWp2AwsBucket($client, $zipped, $directoryName, $restricted);
            }
            // foreach ($directoryContent as $dContIx => $dContVals) {
            //     $directoryName = 'wp-content/' . strrchr($directoryPath, 'wp-content');
            //     // exit($directoryName);
            //     if (!is_array($directoryContent) && file_exists($directoryPath . '/' . $directoryContent)) {
            //         $zipped = $this->gZipFile($directoryPath . '/' . $directoryContent);
            //         $this->putGzipObjectToWp2AwsBucket($client, $zipped, $directoryName, $restricted);

            //     } else {
            //         wp_die(print_r($directoryContent));
            //         wp_die($directoryPath . '/' . $directoryContent);
            //     }
            //     // $this->transferToWp2AwsBucket($client, $directoryName);
            // }
        }

        // wp_die(print_r($directoryNameList));
        // wp_die('All Files Uploaded');
    }




    private function gZipFile($filePath)
    {
        $memory_limit = $this->returnBytes(ini_get('memory_limit'));
        $fileSize = filesize($filePath);
        if ($memory_limit < $fileSize) {
            wpm2awsLogAction('File Too Big: ' . $filePath . ' => ' . $fileSize . 'bytes. Max Allowed: ' . $memory_limit . 'bytes');
            return false;
        }

        $fileContents = null;

        try {
            $fileContents = file_get_contents($filePath);
        } catch (Exception $e) {
            wpm2awsLogAction('Error! Could Not Zip File: ' . $e->gtMessage());
            return false;
        }

        if (empty($fileContents)) {
            return false;
        }

        try {
            $zippedFile = gzencode($fileContents);
        } catch (Exception $e) {
            wpm2awsLogAction('Error! Could Not Zip File: ' . $e->gtMessage());
            return false;
        }

        return $zippedFile;

        // Enter the name of directory
        $pathdir = "Directory Name/";
    }

    public function gZipAndTransferToS3($basePath, $fileName)
    {
        if (defined('WPM2AWS_TESTING') || defined('WPM2AWS_DEBUG') || defined('WPM2AWS_DEV')) {
            wpm2awsLogAction("Debug: Fn gZipAndTransferToS3: ");
        }

        $restricted = true;

        $filepath = '';
        $filepath .= $basePath;
        $pathSeparator = '/';
        if (strpos($filepath, '\\') !== false) {
            $pathSeparator = '\\';
        }
        $filepath .= $pathSeparator;
        $filepath .= $fileName;
        $zippedFile = $this->gZipFile($filepath);



        try {
            $client = new S3Client($this->credentials);
        } catch (ApiException $e) {
            wpm2awsLogAction("API Call Failed! (uploadZipToBucket - api 1). Error Mgs: " . $e->getAwsErrorCode() . '. ' . $e->getAwsErrorMessage());
            return false;
        } catch (Exception $e) {
            wpm2awsLogAction("API Call Failed! (uploadZipToBucket - php 1). Error Mgs: " . $e->getMessage());
            return false;
        }

        $destinationDir = 'wp-content/' . $fileName;

        $uploaded = $this->putGzipObjectToWp2AwsBucket($client, $zippedFile, $destinationDir, $restricted);
        // wpm2awsLogAction("Upload Status: " . $uploaded->get('@metadata')['statusCode']);
        // wpm2awsLogAction("Upload Location: " . $uploaded->get('ObjectURL'));
        return $uploaded;
    }



    public function backgroundUploadFileToBucket($basePath, $fileName, $alternateUploadName = '', $zippedFsUpload = false)
    {
        if (defined('WPM2AWS_TESTING') || defined('WPM2AWS_DEBUG') || defined('WPM2AWS_DEV')) {
            wpm2awsLogAction("Debug: Fn backgroundUploadFileToBucket: ");
        }

        // Confirm that file exists before processing
        if (!file_exists($basePath . DIRECTORY_SEPARATOR . $fileName)) {
            wpm2awsLogAction("Error! File does Not exists: " . $basePath . DIRECTORY_SEPARATOR . $fileName);
            return $status = '404';
        }

        try {
            $client = new S3Client($this->credentials);
        } catch (ApiException $e) {
            wpm2awsLogAction("API Call Failed! (uploadZipToBucket - api 1). Error Mgs: " . $e->getAwsErrorCode() . '. ' . $e->getAwsErrorMessage());
            return $status = '404';
        } catch (Exception $e) {
            wpm2awsLogAction("API Call Failed! (uploadZipToBucket - php 1). Error Mgs: " . $e->getMessage());
            return $status = '404';
        }

        $restricted = true;
        if (false !== get_option('wpm2aws-customer-type') && 'self' === get_option('wpm2aws-customer-type')) {
            $restricted = false;
        }
        $upload = $this->uploadFileToBucket($client, $basePath, $fileName, $restricted, $alternateUploadName, $zippedFsUpload);

        if (defined('WPM2AWS_TESTING') || defined('WPM2AWS_DEBUG') || defined('WPM2AWS_DEV')) {
            wpm2awsLogAction("Upload @ backgroundUploadFileToBucket: " . $upload);
        }

        return $upload;
        // $this->uploadFileToBucketMultiPart($client, $basePath, $fileName, true);
    }

    private function uploadFileToBucket($client, $sourceDir, $sourceFileName, $restricted = true, $alternateUploadName = '', $zippedFsUpload = false)
    {
        // Confirm that file exists before processing
        if (!file_exists($sourceDir . DIRECTORY_SEPARATOR . $sourceFileName)) {
            wpm2awsLogAction("Error! File does Not exists: " . $sourceDir . DIRECTORY_SEPARATOR . $sourceFileName);
            return $status = '404';
        }

        // wpm2awsLogAction('Debug: Fn uploadFileToBucket');

        // $bucketName = ($restricted ? get_option('wpm2aws-aws-s3-default-bucket-name') . 'x' : get_option('wpm2aws-aws-s3-bucket-name'));
        $bucketName = ($restricted ? get_option('wpm2aws-aws-s3-default-bucket-name') : get_option('wpm2aws-aws-s3-bucket-name'));
        // $source = $sourceDir . '/' . $sourceFileName;

        $source = $sourceDir;
        $pathSeparator = '/';
        if (strpos($sourceDir, '\\') !== false) {
            $pathSeparator = '\\';
        }
        $source .= $pathSeparator;
        $source .= $sourceFileName;

        $awsDestinationName = $sourceFileName;
        if ('' !== $alternateUploadName) {
            $awsDestinationName = $alternateUploadName;
        }
        $awsSourceFileName = str_replace('\\', '/', $awsDestinationName);
        $destinationDir = 'wp-content/' . $awsSourceFileName;

        if ('zipped-fs-upload' === $zippedFsUpload) {
            if (defined('WPM2AWS_TESTING') || defined('WPM2AWS_DEBUG') || defined('WPM2AWS_DEV')) {
                wpm2awsLogAction('Debug: Parsing Dest path ');
            }
            $baseFileName = substr($awsSourceFileName, (strrpos($awsSourceFileName, '/') + 1));
            $destinationDir = 'wp-content/' . $baseFileName;
        }


        // $temp_file_location = $_FILES['image']['tmp_name'];
        // wpm2awsLogAction('Debug: Bucket Name => ' . $bucketName);
        // wpm2awsLogAction('Debug: Source => ' . $source);
        if (defined('WPM2AWS_TESTING') || defined('WPM2AWS_DEBUG') || defined('WPM2AWS_DEV')) {
            wpm2awsLogAction('Debug: Zipped FS Upload => ' . $zippedFsUpload);
        }
        if (defined('WPM2AWS_TESTING') || defined('WPM2AWS_DEBUG') || defined('WPM2AWS_DEV')) {
            wpm2awsLogAction('Debug: Destination Dir => ' . $destinationDir);
        }

        try {
            $upload = $client->putObject(
                [
                    'Bucket' => $bucketName,
                    'Key' => $destinationDir,
                    'SourceFile' => $source,
                    'ACL' => 'public-read',
                ]
            );

            // Multipart Upload
            if (defined('WPM2AWS_TESTING') || defined('WPM2AWS_DEBUG') || defined('WPM2AWS_DEV')) {
                wpm2awsLogAction("Status Code: " . $upload->get('@metadata')['statusCode']);
                wpm2awsLogAction("Successful API Call! Upload to Bucket. Bucket Name: " . $bucketName . '. File Name: ' . $destinationDir);
            }
            return $upload->get('@metadata')['statusCode'];
        } catch (ApiException $e) {
            // wp_die($e->get());
            $errorMsg = "API Call Failed! (uploadFileToBucket - API). Error Mgs: " . $e->getMessage() . " | " . $e->getAwsErrorCode() . '. ' . $e->getAwsErrorMessage() . " | " . $bucketName . " | " . $sourceDir . " | " . $sourceFileName;
            wpm2awsLogAction($errorMsg);
            $errors = get_option('wpm2aws_upload_errors');
            $errors[] = $errorMsg;
            wpm2awsAddUpdateOptions('wpm2aws_upload_errors', $errors);
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), __($errorMsg, 'migrate-2-aws'));
            return false;
        } catch (S3Exception $e) {
            $errorMsg = "API Call Failed! (S3 1). Error Mgs: " . $e->get();
            wpm2awsLogAction($errorMsg);
            $errors = get_option('wpm2aws_upload_errors');
            $errors[] = $errorMsg;
            wpm2awsAddUpdateOptions('wpm2aws_upload_errors', $errors);
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), __($errorMsg, 'migrate-2-aws'));
            return false;
        } catch (Exception $e) {
            $errorMsg = "API Call Failed! (uploadFileToBucket - php). Error Mgs: " . $e->getMessage();
            wpm2awsLogAction($errorMsg);
            $errors = get_option('wpm2aws_upload_errors');
            $errors[] = $errorMsg;
            wpm2awsAddUpdateOptions('wpm2aws_upload_errors', $errors);
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), __($errorMsg, 'migrate-2-aws'));
            return false;
        }

        // wpm2awsLogAction('Debug: END: Fn uploadFileToBucket');
    }


    private function uploadFileToBucketMultiPart($client, $sourceDir, $sourceFileName, $restricted = true)
    {
        $bucketName = ($restricted ? get_option('wpm2aws-aws-s3-default-bucket-name') : get_option('wpm2aws-aws-s3-bucket-name'));
        $source = $sourceDir . '/' . $sourceFileName;
        $destinationDir = 'wp-content/' . $sourceFileName;

        // wpm2awsLogAction('Debug: Bucket Name => ' . $bucketName);
        // wpm2awsLogAction('Debug: Source => ' . $source);
        // wpm2awsLogAction('Debug: Destination Dir => ' . $destinationDir);
        if (file_exists($source)) {
            try {
                $result = $client->createMultipartUpload([
                    'Bucket'       => $bucketName,
                    'Key'          => $destinationDir,
                    'StorageClass' => 'STANDARD',
                    'ACL'          => 'public_read',
                    // 'Metadata'     => [
                    //     'param1' => 'value 1',
                    //     'param2' => 'value 2',
                    //     'param3' => 'value 3'
                    // ]
                ]);

                $uploadId = $result['UploadId'];
            } catch (Exception $e) {
                fclose($file);
                $errorMsg = "FAIL; Create Multi-part Upload of {$source} - " . $e->getMessage();
                wpm2awsLogAction($errorMsg);
                $errors = get_option('wpm2aws_upload_errors');
                $errors[] = $errorMsg;
                wpm2awsAddUpdateOptions('wpm2aws_upload_errors', $errors);
                set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), __($errorMsg, 'migrate-2-aws'));
            } catch (S3Exception $e) {
                fclose($file);
                $errorMsg = "FAIL - S3 Error; Create Multi-part Upload of {$source} - " . $e->getAwsErrorCode() . '. ' . $e->getAwsErrorMessage();
                wpm2awsLogAction($errorMsg);
                $errors = get_option('wpm2aws_upload_errors');
                $errors[] = $errorMsg;
                wpm2awsAddUpdateOptions('wpm2aws_upload_errors', $errors);
                set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), __($errorMsg, 'migrate-2-aws'));
            }



            // Upload the file in parts.
            try {
                $file = fopen($source, 'r');
                $partNumber = 1;
                while (!feof($file)) {
                    try {
                        $result = $client->uploadPart([
                            'Bucket'     => $bucketName,
                            'Key'        => $destinationDir,
                            'UploadId'   => $uploadId,
                            'PartNumber' => $partNumber,
                            'Body'       => fread($file, 5 * 1024 * 1024),
                        ]);
                        $parts['Parts'][$partNumber] = [
                            'PartNumber' => $partNumber,
                            'ETag' => $result['ETag'],
                        ];
                        $partNumber++;
                        // wpm2awsLogAction("Uploading part {$partNumber} of {$source}." . PHP_EOL);
                    } catch (Exception $e) {
                        fclose($file);
                        $errorMsg = "FAIL; Multi-part Upload of {$source}- " . $e->getMessage();
                        wpm2awsLogAction($errorMsg);
                        $errors = get_option('wpm2aws_upload_errors');
                        $errors[] = $errorMsg;
                        wpm2awsAddUpdateOptions('wpm2aws_upload_errors', $errors);
                        set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), __($errorMsg, 'migrate-2-aws'));
                    } catch (S3Exception $e) {
                        fclose($file);
                        $errorMsg = "FAIL - S3 Error; Multi-part Upload of {$source} failed - " . $e->getAwsErrorCode() . '. ' . $e->getAwsErrorMessage();
                        wpm2awsLogAction($errorMsg);
                        $errors = get_option('wpm2aws_upload_errors');
                        $errors[] = $errorMsg;
                        wpm2awsAddUpdateOptions('wpm2aws_upload_errors', $errors);
                        set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), __($errorMsg, 'migrate-2-aws'));
                    }
                }
                fclose($file);
            } catch (S3Exception $e) {
                try {
                    $result = $client->abortMultipartUpload([
                        'Bucket'   => $bucketName,
                        'Key'      => $destinationDir,
                        'UploadId' => $uploadId
                    ]);
                } catch (Exception $e) {
                    $errorMsg = "FAIL; Abort Multi-part Upload of {$source} - " . $e->getMessage();
                    wpm2awsLogAction($errorMsg);
                    $errors = get_option('wpm2aws_upload_errors');
                    $errors[] = $errorMsg;
                    wpm2awsAddUpdateOptions('wpm2aws_upload_errors', $errors);
                    set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), __($errorMsg, 'migrate-2-aws'));
                } catch (S3Exception $e) {
                    fclose($file);
                    $errorMsg = "Fail - S3 Error; Abort Multi-part Upload of {$source} - " . $e->getAwsErrorCode() . '. ' . $e->getAwsErrorMessage();
                    wpm2awsLogAction($errorMsg);
                    $errors = get_option('wpm2aws_upload_errors');
                    $errors[] = $errorMsg;
                    wpm2awsAddUpdateOptions('wpm2aws_upload_errors', $errors);
                    set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), __($errorMsg, 'migrate-2-aws'));
                }
                $errorMsg = "FAIL; Upload of {$source} - " . $e->getAwsErrorCode() . '. ' . $e->getAwsErrorMessage();
                wpm2awsLogAction($errorMsg);
                $errors = get_option('wpm2aws_upload_errors');
                $errors[] = $errorMsg;
                wpm2awsAddUpdateOptions('wpm2aws_upload_errors', $errors);
                set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), __($errorMsg, 'migrate-2-aws'));
            }

            // Complete the multipart upload.
            try {
                $result = $client->completeMultipartUpload([
                    'Bucket'   => $bucketName,
                    'Key'      => $destinationDir,
                    'UploadId' => $uploadId,
                    'MultipartUpload' => $parts,
                ]);
                $url = $result['Location'];
                if (defined('WPM2AWS_TESTING') || defined('WPM2AWS_DEBUG') || defined('WPM2AWS_DEV')) {
                    wpm2awsLogAction("COMPLETE; Uploaded {$source} to {$url}");
                }
            } catch (Exception $e) {
                $errorMsg = "FAIL; Complete Multi-part Upload of {$source} - " . $e->getMessage();
                wpm2awsLogAction($errorMsg);
                $errors = get_option('wpm2aws_upload_errors');
                $errors[] = $errorMsg;
                wpm2awsAddUpdateOptions('wpm2aws_upload_errors', $errors);
                set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), __($errorMsg, 'migrate-2-aws'));
            } catch (S3Exception $e) {
                fclose($file);
                $errorMsg = "FAIL - S3 Error; Complete Multi-part Upload of {$source}" . $e->getAwsErrorCode() . '. ' . $e->getAwsErrorMessage();
                wpm2awsLogAction($errorMsg);
                $errors = get_option('wpm2aws_upload_errors');
                $errors[] = $errorMsg;
                wpm2awsAddUpdateOptions('wpm2aws_upload_errors', $errors);
                set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), __($errorMsg, 'migrate-2-aws'));
            }
        } else {
            $errorMsg = "File Not Found: {$source}";
            wpm2awsLogAction($errorMsg);
            $errors = get_option('wpm2aws_upload_errors');
            $errors[] = $errorMsg;
            wpm2awsAddUpdateOptions('wpm2aws_upload_errors', $errors);
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), __($errorMsg, 'migrate-2-aws'));
        }
    }


    private function uploadToWp2AwsBucket($client, $uploadContents)
    {
        try {
            $bucket = $client->putObject(
                [
                    'Bucket' => get_option('wpm2aws-aws-s3-bucket-name'),
                    'Key' => get_option('wpm2aws-aws-s3-upload-directory-name'),
                    'Body' => $uploadContents,
                    'ACL' => 'public-read',
                ]
            );
            wp_die("<strong>Successful API Call!</strong><br><br>Upload to Bucket.<br><br>Bucket Name:<br>" . get_option('wpm2aws-aws-s3-bucket-name') . '<br>File Name: ' . get_option('wpm2aws-aws-s3-upload-directory-name') . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (ApiException $e) {
            wp_die("<strong>API Call Failed! (uploadToWp2AwsBucket)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (S3Exception $e) {
            wp_die("<strong>API Call Failed! (S3 1 - uploadToWp2AwsBucket)</strong><br><br>Error Mgs: " . $e->get() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Exception $e) {
            wp_die("<strong>API Call Failed! (php 2 - uploadToWp2AwsBucket)</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }
    }

    private function returnBytes($sizeString)
    {
        switch (substr($sizeString, -1)) {
            case 'M':
            case 'm':
                return (int)$sizeString * 1048576;
            case 'K':
            case 'k':
                return (int)$sizeString * 1024;
            case 'G':
            case 'g':
                return (int)$sizeString * 1073741824;
            default:
                return $sizeString;
        }
    }

    private function uploadZipToWp2AwsBucket($client, $zipFilePath, $restricted = true)
    {
        // $bucketName = ($restricted ? get_option('wpm2aws-aws-s3-default-bucket-name') . 'x' : get_option('wpm2aws-aws-s3-bucket-name'));
        $bucketName = ($restricted ? get_option('wpm2aws-aws-s3-default-bucket-name') : get_option('wpm2aws-aws-s3-bucket-name'));

        // Using stream instead of file path
        $source = fopen($zipFilePath, 'rb');
        $zipFileName = get_option('wpm2aws-aws-s3-upload-directory-name') . '.zip';

        $parentPath = strpos($zipFilePath, WPM2AWS_ZIP_EXPORT_PATH);
        $wpContentPosition = strpos($zipFilePath, get_option('wpm2aws-aws-s3-upload-directory-name'));


        // $zipFileName = str_replace('\\' , '/', substr($zipFilePath, $wpContentPosition, $parentPath));

        $zipFileName = str_replace('\\', '/', substr($zipFilePath, $wpContentPosition));
        if (defined('WPM2AWS_TESTING') || defined('WPM2AWS_DEBUG') || defined('WPM2AWS_DEV')) {
            wpm2awsLogAction('Zip File Name To S3: ' . $zipFileName);
        }

        try {
            $uploader = new S3ObjectUploader(
                $client,
                $bucketName,
                $zipFileName,
                $source,
                'public-read'
            );
            $str = json_encode($uploader);
            if (defined('WPM2AWS_TESTING') || defined('WPM2AWS_DEBUG') || defined('WPM2AWS_DEV')) {
                wpm2awsLogAction('Uploader Success: ' . $str);
            }
            // return false;
            // wpm2awsLogAction('Successful API Call! (uploadZipToWp2AwsBucket). Upload to Bucket.');
            // return $status = '200';

            // return $uploader;
        } catch (ApiException $e) {
            wpm2awsLogAction("API Call Failed! (new ObjectUpolader 1) - Error Mgs: <br><strong>" . $e->getAwsErrorCode() . ' - ' . $e->getAwsErrorMessage());
        } catch (S3Exception $e) {
            wpm2awsLogAction("API Call Failed! (new ObjectUpolader 2)</strong><br><br>Error Mgs: " . $e->get());
        } catch (Exception $e) {
            wpm2awsLogAction("API Call Failed! (new ObjectUpolader 3)</strong><br><br>Error Mgs: " . $e->getMessage());
        }
        // wp_die(print_r($uploader));
        do {
            try {
                $result = $uploader->upload();
                if ($result["@metadata"]["statusCode"] === '200' || $result["@metadata"]["statusCode"] === 200) {
                    if (defined('WPM2AWS_TESTING') || defined('WPM2AWS_DEBUG') || defined('WPM2AWS_DEV')) {
                        wpm2awsLogAction('File successfully uploaded');
                    }

                    // Push the Zip Log File
                    $logged = $this->uploadFileToBucket($client, WPM2AWS_PLUGIN_DIR . '/inc', 'zipLog.txt', $restricted);

                    return $status = '200';
                } else {
                    return false;
                }
            } catch (MultipartUploadException $e) {
                rewind($source);
                $uploader = new MultipartUploader($client, $source, [
                    'state' => $e->getState(),
                ]);
            }
        } while (!isset($result));


        // try {
        //     $bucket = $client->putObject(
        //         [
        //             'Bucket' => $bucketName,
        //             'Key' => get_option('wpm2aws-aws-s3-upload-directory-name'),
        //             'Body' => $uploadContents,
        //             'ACL' => 'public-read',
        //         ]
        //     );
        //     wp_die("<strong>Successful API Call!</strong><br><br>Upload to Bucket.<br><br>Bucket Name:<br>" . get_option('wpm2aws-aws-s3-bucket-name') . '<br>File Name: ' . get_option('wpm2aws-aws-s3-upload-directory-name') . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        // } catch (ApiException $e) {
        //     wp_die("<strong>API Call Failed! (api 2)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        // } catch (S3Exception $e) {
        //     wp_die("<strong>API Call Failed! (S3 1)</strong><br><br>Error Mgs: " . $e->get() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        // } catch (Exception $e) {
        //     wp_die("<strong>API Call Failed! (php 2)</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        // }
    }


    private function transferToWp2AwsBucket($client, $source, $destination, $restricted = true)
    {
        // AWS Ref:
        // https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/s3-transfer.htm/l
        // ********

        // $bucketName = ($restricted ? get_option('wpm2aws-aws-s3-default-bucket-name') . 'x' : get_option('wpm2aws-aws-s3-bucket-name'));
        $bucketName = ($restricted ? get_option('wpm2aws-aws-s3-default-bucket-name') : get_option('wpm2aws-aws-s3-bucket-name'));

        try {
            // Where the files will be source from
            // $source = '/path/to/source/files';
//            $source = get_option('wpm2aws-aws-s3-upload-directory-path') . '/' . get_option('wpm2aws-aws-s3-upload-directory-name');

            // Where the files will be transferred to
            $dest = 's3://bucket';
//            $subDirPath = $subDirName ? '/' . $subDirName : '';
            $dest = 's3://' . $bucketName . '/' .  $destination;
            if (defined('WPM2AWS_TESTING') || defined('WPM2AWS_DEBUG') || defined('WPM2AWS_DEV')) {
                wpm2awsLogAction('Debug: Dest => ' . $dest);
                wpm2awsLogAction('Debug: source => ' . $source);
            }
            // $dest .= $destination;
            // Create a transfer object
            $transfer = new S3Transfer(
                $client,
                $source,
                $dest,
                [
                    'before' => function (\Aws\Command $command) {
                        // Commands can vary for multipart uploads, so check which command
                        // is being processed
                        if (in_array($command->getName(), ['PutObject', 'CreateMultipartUpload'])) {
                            // Set custom cache-control metadata
                            $command['CacheControl'] = 'max-age=3600';
                            // Apply a canned ACL
                            $command['ACL'] = 'public-read';
                            // $command['ACL'] = strpos($command['Key'], 'CONFIDENTIAL') ### false
                            //     ? 'public-read'
                            //     : 'private';
                        }
                    },
                ]
            );

            $uploaded = $transfer->transfer();

            if (defined('WPM2AWS_TESTING') || defined('WPM2AWS_DEBUG') || defined('WPM2AWS_DEV')) {
                wpm2awsLogAction('Successful API Call! (transferToWp2AwsBucket). Upload to Bucket.');
            }
            return $status = '200';

            // Initiate the transfer and get a promise
            // $promise = $transfer->promise();

            // // Do something when the transfer is complete using the then() method
            // $promise->then(function () {
            //     wpm2awsLogAction('Successful API Call! (transferToWp2AwsBucket). Upload to Bucket.');
            //     return $status = '200';
            // });

            // $promise->otherwise(function ($e) {
            //     wpm2awsLogAction('API Call Failed! (transferToWp2AwsBucket). Error Mgs: ' . $e->getAwsErrorCode() . '. ' . $e->getAwsErrorMessage());
            //     return false;
            // });

            // $manager = new \Aws\S3\Transfer($client, $source, $dest);

//            wp_die("<strong>Successful API Call!</strong><br><br>Upload to Bucket.<br><br>Bucket Name:<br>" . get_option('wpm2aws-aws-s3-bucket-name') . '<br>File Name: ' . get_option('wpm2aws-aws-s3-upload-directory-name') . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (ApiException $e) {
            wpm2awsLogAction('API Call Failed! (transferToWp2AwsBucket). Error Mgs: ' . $e->getAwsErrorCode() . '. ' . $e->getAwsErrorMessage());
            // wp_die("<strong>API Call Failed! (transferToWp2AwsBucket)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
            return false;
        } catch (S3Exception $e) {
            wpm2awsLogAction('API Call Failed! (S3 1 - transferToWp2AwsBucket). Error Mgs: ' . $e->get());
            // wp_die("<strong>API Call Failed! (S3 1)</strong><br><br>Error Mgs: " . $e->get() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
            return false;
        } catch (Exception $e) {
            wpm2awsLogAction('API Call Failed! (php 2 - transferToWp2AwsBucket). Error Mgs: '. $e->getMessage());
            // wp_die("<strong>API Call Failed! (php 2)</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
            return false;
        }
    }

    private function putGzipObjectToWp2AwsBucket($client, $putObject, $subDirName = null, $restricted = true)
    {
        // $bucketName = ($restricted ? get_option('wpm2aws-aws-s3-default-bucket-name') . 'x' : get_option('wpm2aws-aws-s3-bucket-name'));
        $bucketName = ($restricted ? get_option('wpm2aws-aws-s3-default-bucket-name') : get_option('wpm2aws-aws-s3-bucket-name'));

        if (defined('WPM2AWS_TESTING') || defined('WPM2AWS_DEBUG') || defined('WPM2AWS_DEV')) {
            wpm2awsLogAction('Debug: Uploading to Bucket Name: ' . $bucketName);
            wpm2awsLogAction('Debug: Uploading to Bucket Dir: ' . $subDirName);
        }

        try {
            $upload = $client->putObject(
                [
                    'Bucket' => $bucketName,
                    'Key' => $subDirName,
                    'Body' => $putObject,
                    'ACL' => 'public-read',
                    'ContentEncoding' => 'gzip'
                ]
            );

            if (defined('WPM2AWS_DEBUG')) {
                wpm2awsLogAction('Debug: Uploaded : ' . $upload);
            }

            if (defined('WPM2AWS_TESTING') || defined('WPM2AWS_DEBUG') || defined('WPM2AWS_DEV')) {
                wpm2awsLogAction('Upload Status : ' . $upload->get('@metadata')['statusCode']);
                wpm2awsLogAction("Successful API Call! Put gZip to Bucket. Bucket Name: " . get_option('wpm2aws-aws-s3-bucket-name') . '. File Name: ' . get_option('wpm2aws-aws-s3-upload-directory-name'));
            }
        } catch (ApiException $e) {
            wpm2awsLogAction('API Call Failed! (putGzipObjectToWp2AwsBucket). Put gZip to Bucket. Error Mgs:  ' . $e->getAwsErrorCode() . ' | '. $e->getAwsErrorMessage());
            // wp_die("<strong>API Call Failed! (putGzipObjectToWp2AwsBucket)</strong><br><br>Put gZip to Bucket.<br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (S3Exception $e) {
            wpm2awsLogAction('API Call Failed! (S3 1 - putGzipObjectToWp2AwsBucket). Put gZip to Bucket. Error Mgs:  ' . $e->get());
            // wp_die("<strong>API Call Failed! (S3 1)</strong><br><br>Put gZip to Bucket.<br><br>Error Mgs: " . $e->get() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Exception $e) {
            wpm2awsLogAction('API Call Failed! (php 2 - putGzipObjectToWp2AwsBucket). Put gZip to Bucket. Error Mgs:  ' . $e->getMessage());
            // wp_die("<strong>API Call Failed! (php 2)</strong><br><br>Put gZip to Bucket.<br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }
    }


    public function emptyBucket()
    {
        if (false === get_option('wpm2aws-aws-s3-bucket-name')) {
            exit('Error! You must give your S3 Bucket a name before it can be created.<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }
        try {
            $client = new S3Client($this->credentials);
        } catch (ApiException $e) {
            wp_die("<strong>API Call Failed! (api)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Exception $e) {
            wp_die("<strong>API Call Failed!</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }

        $bucketExists = $this->checkBucketExists($client);

        if ($bucketExists) {
            // Get a list of all the objects in the Migrate Directory
            $objects = $this->listObjectsInMigrateFolder($client);

            // If Objects & Objects has Contents
            // Delete Object Individually
            if (!empty($objects) && !empty($objects['Contents'])) {
                foreach ($objects['Contents'] as $objectDetail) {
                    $this->deleteObjectFromMigrateFolder($client, $objectDetail['Key']);
                }
                // Delete the Empty Directory Tree
                $this->deleteUploadDirectory($client);
                wp_die("<strong>Successful API Call!</strong><br><br>Empty Uploaded Directory<br><br>Directory:<br>" . 'wpm2aws_upload/' . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
            } else {
                wp_die('<strong>Empty Directory - No Objects to Delete</strong><br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
            }
        } else {
            wp_die('<strong>Empty Bucket - Bucket Does not Exist (5) </strong><br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }
    }

    private function listObjectsInMigrateFolder($client)
    {
        try {
            $migrateObjects = $client->listObjectsV2([
                'Bucket' => get_option('wpm2aws-aws-s3-bucket-name'),
                'Prefix' => 'wpm2aws_upload/'
            ]);
            return $migrateObjects;
            // wp_die(print_r($migrateObjects['Contents']));
        } catch (ApiException $e) {
            wp_die("<strong>API Call Failed! (api - List Objects In Migrate Folder)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (S3Exception $e) {
            wp_die("<strong>API Call Failed! (S3 - List Objects In Migrate Folder)</strong><br><br>Error Mgs: " . $e->get() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Exception $e) {
            wp_die("<strong>API Call Failed! (php - List Objects In Migrate Folder)</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }
    }

    private function deleteObjectFromMigrateFolder($client, $objectKey)
    {
        if (!empty($objectKey)) {
            try {
                $deletedObject = $client->deleteObject(
                    [
                        'Bucket' => get_option('wpm2aws-aws-s3-bucket-name'),
                        'Key' => $objectKey
                    ]
                );
                // wp_die(print_r($deletedObject));
            } catch (ApiException $e) {
                wp_die("<strong>API Call Failed! (api - Delete Object In Migrate Folder)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
            } catch (S3Exception $e) {
                wp_die("<strong>API Call Failed! (S3 - Delete Object In Migrate Folder)</strong><br><br>Error Mgs: " . $e->get() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
            } catch (Exception $e) {
                wp_die("<strong>API Call Failed! (php - Delete Object In Migrate Folder)</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
            }
        }
    }

    private function deleteUploadDirectory($client)
    {
        try {
            $deletedObject = $client->deleteObject(
                [
                    'Bucket' => get_option('wpm2aws-aws-s3-bucket-name'),
                    'Key' => 'wpm2aws_upload/'
                ]
            );
            // wp_die(print_r($deletedObject));
        } catch (ApiException $e) {
            wp_die("<strong>API Call Failed! (api - Delete Object In Migrate Folder)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (S3Exception $e) {
            wp_die("<strong>API Call Failed! (S3 - Delete Object In Migrate Folder)</strong><br><br>Error Mgs: " . $e->get() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Exception $e) {
            wp_die("<strong>API Call Failed! (php - Delete Object In Migrate Folder)</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }
    }

    public function createLightsail($addition = false)
    {
        // - Must be at least 2 characters long
        // - Can contain alphanumerics
        // - Can contain underscores (_)
        // - Can contain dashes (-)
        // - Can contain periods (.)
        // - Cannot Contain other than listed above

        if (false === get_option('wpm2aws-aws-lightsail-name')) {
            wp_die('Error! You must give your AWS Instance a name before it can be created.<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }
        $launchInstanceName = get_option('wpm2aws-aws-lightsail-name');

        if (false !== get_option('wpm2aws-aws-lightsail-region') && '' !== get_option('wpm2aws-aws-lightsail-region')) {
            $lightsailRegion = get_option('wpm2aws-aws-lightsail-region');
            $this->credentials['region'] = $lightsailRegion;
        }

        try {
            $lightsailClient = new LightsailClient($this->credentials);
        } catch (ApiException $e) {
            wp_die("<strong>API Call Failed! (api - Create AWS Client)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Exception $e) {
            wp_die("<strong>API Call Failed! (php - Create AWS Client)</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }

        $regions = $this->getLightsailRegions($lightsailClient);

        $availableRegion = $this->lightsailRegionAvailability($regions);

        if (!$availableRegion) {
            wp_die('No LightSail Region');
        }
        $launchAvailabilityZoneId = $this->getAvailabilityZone($availableRegion);

        // wp_die($launchAvailabilityZoneId);


        if (!$launchAvailabilityZoneId) {
            wp_die('No Availability Zone in Region: ' . $availableRegion['region']);
        }
        // wp_die(print_r($launchAvailabilityZoneId));
        $amiBlueprint = $this->getLightsailBluePrints($lightsailClient);

        if (!$amiBlueprint) {
            wp_die('No WordPress AMI Available from Lightsail');
        }
        // wp_die(print_r($amiBlueprint));
        $launchAmiBlueprintId = $amiBlueprint['bpid'];



        // Instance Bundle
        $bundles = $this->getLightsailBundles($lightsailClient);
        if (!$bundles) {
            wp_die('No Instance Bundles Available from Lightsail');
        }

        $instanceBundle = $this->getBundle($bundles);
        if (!$instanceBundle) {
            wp_die('No Launch Bundle Available');
        }
        // wp_die(print_r($instanceBundle));
        $launchInstanceBundleId = $instanceBundle['bundleId'];

        $launchParams = array(
            'AvailabilityZone' => $launchAvailabilityZoneId,
            'AmiBluePrint' => $launchAmiBlueprintId,
            'InstanceBundleID' => $launchInstanceBundleId
        );
        // wp_die(print_r($launchParams));


        if ('admin' === $addition || 'zipped' === $addition) {
            $scriptToUploadFilesFromS3 = $this->getZippedLaunchScript(
                $this->credentials['credentials']['key'],
                $this->credentials['credentials']['secret'],
                $this->credentials['region'],
                get_option('wpm2aws-aws-s3-bucket-name')
            );
        } else {
            $scriptToUploadFilesFromS3 = $this->getLaunchScript(
                $this->credentials['credentials']['key'],
                $this->credentials['credentials']['secret'],
                $this->credentials['region'],
                get_option('wpm2aws-aws-s3-bucket-name')
            );
        }

        // Make or get key pair for user
        $keyPairName = $this->getKeyPairName($lightsailClient);

        $lightsailStatus = $this->launchLightsail(
            $lightsailClient,
            $launchInstanceName,
            $launchAvailabilityZoneId,
            $launchAmiBlueprintId,
            $launchInstanceBundleId,
            $scriptToUploadFilesFromS3,
            $keyPairName
        );

        // sleep(10);

        // $lightsailUrl = $this->getInstanceDetails($lightsailClient, $launchInstanceName, 'url');

        // wp_die(print_r($lightsailUrl));






        while ($lightsailStatus !== 'Running') {
            $instance = $this->getInstanceDetails($lightsailClient, $launchInstanceName);
            if (empty($instance['publicIpAddress'])) {
                sleep(2);
            } else {
                $lightsailStatus = 'Running';
            }
        }

        $lightsailStatus = $this->getInstanceState($lightsailClient, $launchInstanceName);
        while ($lightsailStatus !== 'running') {
            sleep(2);
            $lightsailStatus = $this->getInstanceState($lightsailClient, $launchInstanceName);
            // if ($lightsailStatus !== 'running') {
            //     sleep(2);
            // // } else {
            // //     $lightsailStatus = 'Running';
            // }
        }

        $accessControl = $this->getInstanceAccessDetails($lightsailClient, $launchInstanceName, 'password');

        $ip = $instance['publicIpAddress'];
        $region = $instance['location']['regionName'];
        $az = $instance['location']['availabilityZone'];
        $name = $instance['name'];



        // $peered = $this->checkPeeredWithVPC($lightsailClient);

        // while (!$peered) {
        //     sleep(2);
        //     $peered = $this->peerWithVPC($lightsailClient);
        //     if ($peered['operation']['status']!== 'Succeeded') {
        //         $peered = true;
        //     }
        // }

        // $instanceDetails = $this->describeInstanceDetails();

        // New IAM
        // $instanceID = $instance['supportCode'];
        // if (($breakPoint = strpos($instanceID, "/")) !== false) {
        //     $instanceID = substr($instanceID, $breakPoint+1);
        // } else {
        //     wp_die('Could not find Instance Id from : ' . $instanceID);
        // }
        // $attachRole = $this->attachRoleToLightsail($instanceID);

        // $accessDetails = $this->getLightsailAccess($lightsailClient, $name);





        // Add Role to Instance




        // $peered = $this->peerWithVPC($lightsailClient);
        // wp_die($peered['operation']['status']);
        // while ($peered['operation']['status']!== 'Succeeded') {
        //     sleep(2);
        //     $peered = $this->peerWithVPC($lightsailClient);
        // }

        // wp_die(print_r($instance));

        wpm2awsAddUpdateOptions(
            'wpm2aws-lightsail-instance-details',
            array(
                'name' => $name,
                'region' => $region,
                'publicIp' => $ip,
                'accessControl' => $accessControl,
                'details' => $instance
            )
        );
        
        wpm2awsLogRAction('wpm2aws_lightsail_details', 'Name => ' . $name . ', Region => ' . $region . ', IP: ' . $ip);

        return $instance;

        wp_die('AWS Instance Running<br>Name: ' . $name . '<br>Region/AZ: ' . $region . '/' . $az . '<br>Address: <a href="http://' . $ip . '/" target="_blank">' . $ip . '</a>');

        wp_die(print_r($lightsailUrl));


        wp_die('Dev Lightsail. AWS Name: ' . get_option('wpm2aws-aws-lightsail-name'));
    }

    private function getLaunchScript_ORIGINAL($key, $secret, $region, $bucketName)
    {
        // $scriptToUploadFilesFromS3 = "#!/bin/bash; ";
        // $scriptToUploadFilesFromS3 = "";
        // run as root so do not use sudo
        // $scriptToUploadFilesFromS3 .= "apt-get -y install awscli";
        // $scriptToUploadFilesFromS3 .= " && ";
        // $scriptToUploadFilesFromS3 .= "aws configure set aws_access_key_id " . $this->credentials['credentials']['key'];
        // $scriptToUploadFilesFromS3 .= " && ";
        // $scriptToUploadFilesFromS3 .= "aws configure set aws_secret_access_key " . $this->credentials['credentials']['secret'];
        // $scriptToUploadFilesFromS3 .= " && ";
        // $scriptToUploadFilesFromS3 .= "aws configure set default.region " . $this->credentials['region'];
        // $scriptToUploadFilesFromS3 .= " && ";
        // $scriptToUploadFilesFromS3 .= "aws configure set default.output text";


        // $scriptToUploadFilesFromS3 .= " && ";
        // $scriptToUploadFilesFromS3 = "aws s3 cp s3://" . get_option('wpm2aws-aws-s3-bucket-name') . " /home/bitnami/apps/wordpress/htdocs/wp-content --region " . $this->credentials['region'] . " --recursive";


//         curl "https://d1vvhvl2y92vvt.cloudfront.net/awscli-exe-linux-x86_64.zip" -o "awscliv2.zip"
//         unzip awscliv2.zip
//         sudo ./aws/install
//         /usr/bin/aws

//         # update package list
//         apt-get –y update
//         # download wordpress plugin
//         wget "https://downloads.wordpress.org/plugin/seahorse-gdpr-data-manager.zip"
//         apt-get –y install unzip
//         # unzip into wordpress plugin directory
//         unzip seahorse-gdpr-data-manager.zip -d /home/bitnami/apps/wordpress/htdocs/wp-content/plugins


        // # update package list
        // apt-get –y update
        // # download and install aws cli
        // wget "https://d1vvhvl2y92vvt.cloudfront.net/awscli-exe-linux-x86_64.zip"
        // apt-get –y install unzip
        // # unzip into correct directory
        // unzip awscli-exe-linux-x86_64.zip -d /usr/bin
        // /usr/bin/aws/install


        // $scriptToUploadFilesFromS3 = "apt-get -y install awscli";
        // $scriptToUploadFilesFromS3 = "#!/bin/bash; ";


        $scriptToUploadFilesFromS3 = '';
        $scriptToUploadFilesFromS3 .= 'apt-get –y update;';
        $scriptToUploadFilesFromS3 .= 'wget "https://d1vvhvl2y92vvt.cloudfront.net/awscli-exe-linux-x86_64.zip";';

        $scriptToUploadFilesFromS3 .= 'apt-get –y install unzip;';
        $scriptToUploadFilesFromS3 .= 'unzip awscli-exe-linux-x86_64.zip -d /usr/bin;';
        $scriptToUploadFilesFromS3 .= '/usr/bin/aws/install;';
        $scriptToUploadFilesFromS3 .= 'rm awscli-exe-linux-x86_64.zip;';

        // $scriptToUploadFilesFromS3 .= 'wget "https://wpm2aws-test-create-new-bucket.s3.eu-central-1.amazonaws.com/credentials" -P /home/bitnami/.aws;';
        // $scriptToUploadFilesFromS3 .= 'wget "https://wpm2aws-test-create-new-bucket.s3.eu-central-1.amazonaws.com/config" -P /home/bitnami/.aws;';

        // $scriptToUploadFilesFromS3 .= 'wget "https://wpm2aws-test-create-new-bucket.s3.eu-central-1.amazonaws.com/credentials" -P /root/.aws;';
        // $scriptToUploadFilesFromS3 .= 'wget "https://wpm2aws-test-create-new-bucket.s3.eu-central-1.amazonaws.com/config" -P /root/.aws;';

        $scriptToUploadFilesFromS3 .= 'aws2 configure set aws_access_key_id ' . $key . ';';
        $scriptToUploadFilesFromS3 .= 'aws2 configure set aws_secret_access_key ' . $secret . ';';
        $scriptToUploadFilesFromS3 .= 'aws2 configure set default.region ' . $region . ';';



        $scriptToUploadFilesFromS3 .= 'aws2 configure set default.output json;';

        // $scriptToUploadFilesFromS3 .= 'aws2 s3 cp s3://wpm2aws-test-create-new-bucket /home/bitnami/apps/wordpress/htdocs/wp-content --region eu-central-1 --recursive;';

        /**
         * **** PREVIOUS WORKING *****
         */
        // $scriptToUploadFilesFromS3 .= 'aws2 s3 cp s3://' . $bucketName . '/wpm2-aws-uploads/wp_content /home/bitnami/apps/wordpress/htdocs/wp-content --region  ' . $region . ' --recursive;';
        // $scriptToUploadFilesFromS3 .= 'aws2 s3 cp s3://' . $bucketName . '/wpm2-aws-uploads/wp_db/bitnami_wordpress.sql /bitnami_wordpress.sql --region  ' . $region . ';';
        /**
         * **** END: PREVIOUS WORKING *****
         */

        $scriptToUploadFilesFromS3 .= 'aws2 s3 cp s3://' . $bucketName . '/wp-content /home/bitnami/apps/wordpress/htdocs/wp-content --region  ' . $region . ' --recursive;';
        // $scriptToUploadFilesFromS3 .= 'aws2 s3 cp s3://' . $bucketName . '/wp-content/plugins/wp-migrate-2-aws/libraries/db/db.sql /bitnami_wordpress.sql --region  ' . $region . ';';


        // \home\bitnami\apps\wordpress\htdocs\wp-content\plugins\wp-migrate-2-aws\libraries\db\dbIiporter.php
        // php /home/bitnami/apps/wordpress/htdocs/wp-content/plugins/wp-migrate-2-aws/libraries/db/dbIiporter.php
        // C:\xampp74\htdocs\wordpressTest\wp-content\plugins\wp-migrate-2-aws\libraries\db\dbIiporter.php

        // bitnami_wordpress
        // bn_wordpress
        // localhost:3306
        // 11c55066c5
        // cat ./bitnami_credentials


        // $scriptToUploadFilesFromS3 .= ' export PASSWORD=`cat /home/bitnami/bitnami_application_password`;';
        // $scriptToUploadFilesFromS3 .= 'PASSWORD="$(cat /home/bitnami/bitnami_application_password)";';
        // cat bitnami_application_password
        // lqCuFD6O6Zru
        // $scriptToUploadFilesFromS3 .= ' mysql -uroot -p$PASSWORD -D bitnami_wordpress < /bitnami_wordpress.sql;';
        // $scriptToUploadFilesFromS3 .= '/opt/bitnami/mysql/bin/mysql -u "root" -p"$(cat /home/bitnami/bitnami_application_password)" "bitnami_wordpress" -f < /bitnami_wordpress.sql;';
        // "$(cat /home/bitnami/bitnami_application_password)"
        // /opt/bitnami/mysql/bin/mysql_config_editor set --login-path=local --host=localhost --user=root --password;
        // /opt/bitnami/mysql/bin/mysql_config_editor print --login-path=client
        // $scriptToUploadFilesFromS3 .= 'echo "[client]" > /home/bitnami/.mylogin.cnf;';
        // $scriptToUploadFilesFromS3 .= 'echo "user=root" >> /home/bitnami/.mylogin.cnf;';
        // $scriptToUploadFilesFromS3 .= 'echo "password="$(cat /home/bitnami/bitnami_application_password) >> /home/bitnami/.mylogin.cnf;';
        // $scriptToUploadFilesFromS3 .= 'echo "host=localhost" >> /home/bitnami/.mylogin.cnf;';
        // $scriptToUploadFilesFromS3 .= 'chmod 600 /home/bitnami/.mylogin.cnf;';

        // $scriptToUploadFilesFromS3 .= 'cat /opt/bitnami/mysql/.my.cnf >> /opt/bitnami/mysql/.my.cnf;';
        // $scriptToUploadFilesFromS3 .= 'echo "[client]" >> /opt/bitnami/mysql/.my.cnf;';

        // $scriptToUploadFilesFromS3 .= 'echo "user=root" >> /opt/bitnami/mysql/.my.cnf;';
        // $scriptToUploadFilesFromS3 .= 'echo "password="$(cat /home/bitnami/bitnami_application_password) >> /opt/bitnami/mysql/.my.cnf;';

        // $scriptToUploadFilesFromS3 .= '[ -f /opt/bitnami/mysql/.my.cnf ] && echo "exist" >> /opt/bitnami/mysql/.my.cnf || echo "does not exist" >> /opt/bitnami/mysql/.my.cnf;';


        // mysql --login-path=local
        // $scriptToUploadFilesFromS3 .= 'echo "$(cat /home/bitnami/bitnami_application_password)" >> /home/bitnami/.mylogin.cnf;';
        // 9KBYSDi116HS


        // OLD
        // $scriptToUploadFilesFromS3 .= '/opt/bitnami/mysql/bin/mysql -u "root" -p"$(cat /home/bitnami/bitnami_application_password)" -D "bitnami_wordpress" -f < /bitnami_wordpress.sql;';

        // Full DB
        // $scriptToUploadFilesFromS3 .= '/opt/bitnami/php/bin/php /home/bitnami/apps/wordpress/htdocs/wp-content/plugins/wp-migrate-2-aws/libraries/db/dbIiporter.php';


        // DB TABLES
        $scriptToUploadFilesFromS3 .= '/opt/bitnami/php/bin/php /home/bitnami/apps/wordpress/htdocs/wp-content/plugins/wp-migrate-2-aws-launcher/libraries/unzip/zipIimporter_fs.php';

        // DB TABLES
        $scriptToUploadFilesFromS3 .= '/opt/bitnami/php/bin/php /home/bitnami/apps/wordpress/htdocs/wp-content/plugins/wp-migrate-2-aws/libraries/db/dbIiporter_tables_db.php';


        // $scriptToUploadFilesFromS3 .= ' export PASSWORD=;';

        // /opt/bitnami/var/log

        // PASSWORD=$(cat /home/bitnami/bitnami_application_password);mysql --user=root --password=$PASSWORD

        // themes
        // plugins
        // languages
        // uploads
        // updgrade



        // ****  Interface Script
        // apt-get –y update
        // wget "https://d1vvhvl2y92vvt.cloudfront.net/awscli-exe-linux-x86_64.zip"
        // apt-get –y install unzip
        // unzip awscli-exe-linux-x86_64.zip -d /usr/bin
        // /usr/bin/aws/install
        // rm awscli-exe-linux-x86_64.zip
        // wget "https://wpm2aws-test-create-new-bucket.s3.eu-central-1.amazonaws.com/credentials" -P /home/bitnami/.aws
        // wget "https://wpm2aws-test-create-new-bucket.s3.eu-central-1.amazonaws.com/config" -P /home/bitnami/.aws
        // wget "https://wpm2aws-test-create-new-bucket.s3.eu-central-1.amazonaws.com/credentials" -P /root/.aws
        // wget "https://wpm2aws-test-create-new-bucket.s3.eu-central-1.amazonaws.com/config" -P /root/.aws
        // aws2 s3 cp s3://wpm2aws-test-create-new-bucket /home/bitnami/apps/wordpress/htdocs/wp-content --region eu-central-1 --recursive













        // {
        //     "Version": "2012-10-17",
        //     "Id": "Policy1573828443281",
        //     "Statement": [
        //         {
        //             "Sid": "Stmt1573828355511",
        //             "Effect": "Allow",
        //             "Principal": {
        //                 "Federated": "172.26.2.231/32"
        //             },
        //             "Action": "s3:*",
        //             "Resource": [
        //                 "arn:aws:s3:::wpm2aws-test-create-new-bucket",
        //                 "arn:aws:s3:::wpm2aws-test-create-new-bucket/credentials.txt"
        //             ]
        //         }
        //     ]
        // }


        // This Policy Works
        // {
        //     "Version": "2012-10-17",
        //     "Id": "S3PolicyMigrate",
        //     "Statement": [
        //         {
        //             "Sid": "IPAllow",
        //             "Effect": "Allow",
        //             "Principal": "*",
        //             "Action": "s3:*",
        //             "Resource": [
        //                 "arn:aws:s3:::wpm2aws-test-create-new-bucket/credentials",
        //                 "arn:aws:s3:::wpm2aws-test-create-new-bucket/config"
        //             ],
        //             "Condition": {
        //                 "IpAddress": {
        //                     "aws:SourceIp": "35.159.32.66/32"
        //                 }
        //             }
        //         }
        //     ]
        // }


        // $scriptToUploadFilesFromS3 .= 'aws2 configure set aws_access_key_id ' . $this->credentials['credentials']['key'] . ';';
        // $scriptToUploadFilesFromS3 .= 'aws2 configure set aws_secret_access_key ' . $this->credentials['credentials']['secret'] . ';';
        // $scriptToUploadFilesFromS3 .= 'aws2 configure set default.region ' . $this->credentials['region'] . ';';
        // $scriptToUploadFilesFromS3 .= 'aws2 configure set default.output json;';
        // $scriptToUploadFilesFromS3 .= 'aws s3 cp s3://' . get_option('wpm2aws-aws-s3-bucket-name') . ' /home/bitnami/apps/wordpress/htdocs/wp-content --region ' . $this->credentials['region'] . ' --recursive;';

        // $scriptToUploadFilesFromS3 = "#!/bin/bash; ";
        // $scriptToUploadFilesFromS3 = 'apt-get -y install awscli';



        // 18.196.187.88
        // arn:aws:lightsail:eu-central-1:786540766804:Instance/3565eb91-b3fd-4887-85af-bee5f1fc9caf

        return $scriptToUploadFilesFromS3;
    }

    private function getLaunchScript($key, $secret, $region, $bucketName)
    {
        $scriptToUploadFilesFromS3 = '';
        $scriptToUploadFilesFromS3 .= 'apt-get -y awscli;';

        $scriptToUploadFilesFromS3 .= 'wget "https://d1vvhvl2y92vvt.cloudfront.net/awscli-exe-linux-x86_64.zip";';
        $scriptToUploadFilesFromS3 .= 'apt-get –y install unzip;';
        $scriptToUploadFilesFromS3 .= 'unzip awscli-exe-linux-x86_64.zip -d /usr/bin;';
        $scriptToUploadFilesFromS3 .= '/usr/bin/aws/install;';
        $scriptToUploadFilesFromS3 .= 'rm awscli-exe-linux-x86_64.zip;';

        $scriptToUploadFilesFromS3 .= 'aws configure set aws_access_key_id ' . $key . ';';
        $scriptToUploadFilesFromS3 .= 'aws configure set aws_secret_access_key ' . $secret . ';';
        $scriptToUploadFilesFromS3 .= 'aws configure set default.region ' . $region . ';';

        $scriptToUploadFilesFromS3 .= 'aws configure set default.output json;';

        $scriptToUploadFilesFromS3 .= 'aws s3 cp s3://' . $bucketName . '/wp-content /home/bitnami/apps/wordpress/htdocs/wp-content --region  ' . $region . ' --recursive;';

        // Run the Importer

        // Change Permission to run processes
        $scriptToUploadFilesFromS3 .= 'chown -R bitnami /home/bitnami/apps/wordpress/htdocs/wp-content/plugins/wp-migrate-2-aws-launcher;';
        $scriptToUploadFilesFromS3 .= 'chmod -R 775 /home/bitnami/apps/wordpress/htdocs/wp-content/plugins/wp-migrate-2-aws-launcher;';

        $scriptToUploadFilesFromS3 .= 'chown -R bitnami /home/bitnami/apps/wordpress/htdocs/wp-content/plugins/wpm2aws-zips;';
        $scriptToUploadFilesFromS3 .= 'chmod -R 775 /home/bitnami/apps/wordpress/htdocs/wp-content/plugins/wpm2aws-zips;';

        // Unzip the Files
        $scriptToUploadFilesFromS3 .= '/opt/bitnami/php/bin/php /home/bitnami/apps/wordpress/htdocs/wp-content/plugins/wp-migrate-2-aws-launcher/libraries/unzip/zipIimporter_fs.php;';

        // DB TABLES
        $scriptToUploadFilesFromS3 .= '/opt/bitnami/php/bin/php /home/bitnami/apps/wordpress/htdocs/wp-content/plugins/wp-migrate-2-aws/libraries/db/dbIiporter_tables_db.php;';


        // apt-get -y awscli;
        // wget "https://d1vvhvl2y92vvt.cloudfront.net/awscli-exe-linux-x86_64.zip";
        // apt-get –y install unzip;
        // unzip awscli-exe-linux-x86_64.zip -d /usr/bin;
        // /usr/bin/aws/install;
        // rm awscli-exe-linux-x86_64.zip;
        // aws configure set aws_access_key_id AKIA3OIL3UZKN45I2JXF;
        // aws configure set aws_secret_access_key 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;
        // aws configure set default.region eu-west-1;
        // aws configure set default.output json;
        // aws s3 cp s3://wp-migrate-2-aws-wpm2aws-info-seahorse-data-com/wp-content /home/bitnami/apps/wordpress/htdocs/wp-content --region  eu-west-1 --recursive;
        // /opt/bitnami/php/bin/php /home/bitnami/apps/wordpress/htdocs/wp-content/plugins/wp-migrate-2-aws/libraries/db/dbIiporter_tables_db.php;




        return $scriptToUploadFilesFromS3;
    }


    private function getZippedLaunchScript($key, $secret, $region, $bucketName)
    {
        $scriptToUploadFilesFromS3 = '';

        // Pull in AWS CLI
        $scriptToUploadFilesFromS3 .= 'apt-get -y awscli;';
        $scriptToUploadFilesFromS3 .= 'wget "https://d1vvhvl2y92vvt.cloudfront.net/awscli-exe-linux-x86_64.zip";';
        $scriptToUploadFilesFromS3 .= 'apt-get –y install unzip;';
        $scriptToUploadFilesFromS3 .= 'unzip awscli-exe-linux-x86_64.zip -d /usr/bin;';
        $scriptToUploadFilesFromS3 .= '/usr/bin/aws/install;';
        $scriptToUploadFilesFromS3 .= 'rm awscli-exe-linux-x86_64.zip;';

        // Configure AWS CLI
        $scriptToUploadFilesFromS3 .= 'aws configure set aws_access_key_id ' . $key . ';';
        $scriptToUploadFilesFromS3 .= 'aws configure set aws_secret_access_key ' . $secret . ';';
        $scriptToUploadFilesFromS3 .= 'aws configure set default.region ' . $region . ';';
        $scriptToUploadFilesFromS3 .= 'aws configure set default.output json;';

        // Get Data From Bucket
        $scriptToUploadFilesFromS3 .= 'aws s3 cp s3://' . $bucketName . '/wp-content/wpm2aws-zips.zip /home/bitnami/apps/wordpress/htdocs/ --region  ' . $region . ';';
        $scriptToUploadFilesFromS3 .= 'aws s3 cp s3://' . $bucketName . '/wp-content/zipIimporter_dlfs.php /home/bitnami/apps/wordpress/htdocs/ --region  ' . $region . ';';
        $scriptToUploadFilesFromS3 .= 'aws s3 cp s3://' . $bucketName . '/wp-content/zipLog.txt /home/bitnami/apps/wordpress/htdocs/ --region  ' . $region . ';';

        // Unzip Download
        $scriptToUploadFilesFromS3 .= 'unzip /home/bitnami/apps/wordpress/htdocs/wpm2aws-zips.zip -d /home/bitnami/apps/wordpress/htdocs/wpm2aws-zips;';

        // Change Permission to run processes
        $scriptToUploadFilesFromS3 .= 'chown -R bitnami /home/bitnami/apps/wordpress/htdocs/wpm2aws-zips;';
        $scriptToUploadFilesFromS3 .= 'chmod -R 775 /home/bitnami/apps/wordpress/htdocs/wpm2aws-zips;';

        $scriptToUploadFilesFromS3 .= 'chown bitnami /home/bitnami/apps/wordpress/htdocs/zipIimporter_dlfs.php;';
        $scriptToUploadFilesFromS3 .= 'chmod -R 775 /home/bitnami/apps/wordpress/htdocs/zipIimporter_dlfs.php;';

        $scriptToUploadFilesFromS3 .= 'chown -R bitnami /home/bitnami/apps/wordpress/htdocs/zipLog.txt;';
        $scriptToUploadFilesFromS3 .= 'chmod -R 775 /home/bitnami/apps/wordpress/htdocs/zipLog.txt;';

        // Extract Downloads to relevant locations (based on zipLog.txt file)
        $scriptToUploadFilesFromS3 .= '/opt/bitnami/php/bin/php /home/bitnami/apps/wordpress/htdocs/zipIimporter_dlfs.php;';

        // Run DB Import
        $scriptToUploadFilesFromS3 .= '/opt/bitnami/php/bin/php /home/bitnami/apps/wordpress/htdocs/wp-content/plugins/wp-migrate-2-aws/libraries/db/dbIiporter_tables_db.php;';

        // Change Permission of newly added files / Directories
        $scriptToUploadFilesFromS3 .= 'chown -R -f daemon:daemon /home/bitnami/apps/wordpress/htdocs/wp-content;';


        // Remove Data files
        $scriptToUploadFilesFromS3 .= 'rm -R -f /home/bitnami/apps/wordpress/htdocs/wpm2aws-zips;';
        $scriptToUploadFilesFromS3 .= 'rm /home/bitnami/apps/wordpress/htdocs/wpm2aws-zips.zip;';
        $scriptToUploadFilesFromS3 .= 'rm /home/bitnami/apps/wordpress/htdocs/zipIimporter_dlfs.php;';
        $scriptToUploadFilesFromS3 .= 'rm /home/bitnami/apps/wordpress/htdocs/zipLog.txt;';
        // $scriptToUploadFilesFromS3 .= 'rm /home/bitnami/apps/wordpress/htdocs/zipLog.txt;';

        $scriptToUploadFilesFromS3 .= 'rm -R -f /home/bitnami/apps/wordpress/htdocs/wp-content/plugins/wp-migrate-2-aws/libraries/db/tables;';

        return $scriptToUploadFilesFromS3;
    }


    // private function getLightsailAccess($lightsailClient, $name)
    // {
    //     try {
    //         $accessDetails = $lightsailClient->getInstanceAccessDetails([
    //             'instanceName' => $name, // REQUIRED
    //             // 'protocol' => 'ssh|rdp',
    //         ]);

    //         wp_die(print_r($accessDetails));
    //         return $peered;
    //     } catch (LightsailException $e) {
    //         wp_die("<strong>API Call Failed! (AWS Access)</strong><br><br>Error Mgs: " . $e->get() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
    //     } catch (ApiException $e) {
    //         wp_die("<strong>API Call Failed! (api 1)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
    //     } catch (Exception $e) {
    //         wp_die("<strong>API Call Failed! (php 1)</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
    //     }
    // }




    private function peerWithVPC($lightsailClient)
    {
        try {
            $peered = $lightsailClient->peerVpc(
                [
                ]
            );

            // wp_die(print_r($peered));
            return $peered;
        } catch (ApiException $e) {
            wp_die("<strong>API Call Failed! (peerWithVPC - api 1)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (LightsailException $e) {
            wp_die("<strong>API Call Failed! (peerWithVPC - AWS Peering)</strong><br><br>Error Mgs: " . $e->get() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Exception $e) {
            wp_die("<strong>API Call Failed! (peerWithVPC - php 1)</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }
    }

    private function checkPeeredWithVPC($lightsailClient)
    {
        try {
            $peered = $lightsailClient->isVpcPeered(
                [
                ]
            );
            // wp_die(print_r($peered));
            return $peered['isPeered'];
        } catch (ApiException $e) {
            wp_die("<strong>API Call Failed! (checkPeeredWithVPC - api 1)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (LightsailException $e) {
            wp_die("<strong>API Call Failed! (checkPeeredWithVPC - AWS Check Peering)</strong><br><br>Error Mgs: " . $e->get() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Exception $e) {
            wp_die("<strong>API Call Failed! (checkPeeredWithVPC - php 1)</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }
    }

    private function getLightsailRegions($lightsailClient)
    {
        // LightSail (get Regions)
        try {
            $regions = $lightsailClient->getRegions(
                [
                    'includeAvailabilityZones' => true,
                    'includeRelationalDatabaseAvailabilityZones' => false,
                ]
            );
        } catch (ApiException $e) {
            wp_die("<strong>API Call Failed! (api - Get AWS Regions)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Exception $e) {
            wp_die("<strong>API Call Failed! (php - Get AWS Regions)</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }

        return $regions;
        // wp_die(print_r($regions));
        // wp_die('Dev Lightsail. AWS Name: ' . get_option('wpm2aws-aws-lightsail-name'));
    }

    private function lightsailRegionAvailability($regions, $preferredRegion = null)
    {
        if (false === get_option('wpm2aws-aws-region')) {
            wp_die('Error! You must select a region for your AWS Instance before it can be created.<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }

        $selectedRegion = $preferredRegion;
        if (!$selectedRegion) {
            // $selectedRegion = get_option('wpm2aws-aws-region');
            if (false !== get_option('wpm2aws-aws-lightsail-region') && '' !== get_option('wpm2aws-aws-lightsail-region')) {
                $selectedRegion = get_option('wpm2aws-aws-lightsail-region');
            } else {
                $selectedRegion = get_option('wpm2aws-aws-region');
            }
        }

        foreach ($regions['regions'] as $regionIx => $regionDetails) {
            if ($regionDetails['name'] === $selectedRegion) {
                return $region = array(
                    'region' => $regionDetails['name'],
                    'azs' => $regionDetails['availabilityZones']
                );
                // wp_die('Your AWS Instance will be Created as follows:<br>Region: ' . $regionDetails['displayName'] . ' (' . $regionDetails['name'] . ')<br>' . $regionDetails['description']);
            }
        }
        return false;
        // wp_die('No AWS Region Available for: ' . get_option('wpm2aws-aws-region'));
    }

    private function getKeyPairName($lightsailClient)
    {
        // If Self Managed
        // Return False
        // Tells System to Use Default
        if (false !== get_option('wpm2aws-customer-type') && 'self' === get_option('wpm2aws-customer-type')) {
            return false;
        }

        // If Trial User
        // Return False
        // Tells System to Use Default
        if (
            false !==  get_option('wpm2aws_valid_licence_type') &&
            'TRIAL' === strtoupper(get_option('wpm2aws_valid_licence_type'))
        ) {
            return false;
        }
        
        $userName = get_option('wpm2aws-iam-user');
        
        $existing = $this->getExistingKeyPair($lightsailClient, $userName);

        if (false !== $existing) {
            return $existing['name'];
        }

        $new = $this->makeNewKeyPair($lightsailClient, $userName);

        if (false !== $new) {
            return $new['name'];
        }

        return false;
    }


    private function getExistingKeyPair($lightsailClient, $userName)
    {
        $keypairname = 'wpm2aws-key-' . $userName;
        
        try {
            $keyPair = $lightsailClient->getKeyPair(
                [
                    'keyPairName' => $keypairname, // '<string>', // REQUIRED
                ]
            );
            wpm2awsLogRAction('wpm2aws_use_existing_key_pair', 'Key-Pair Name: ' . $keypairname);
            return $keyPair['keyPair'];
        } catch (ApiException $e) {
            return false;
        } catch (LightsailException $e) {
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    private function makeNewKeyPair($lightsailClient, $userName)
    {
        $keypairname = 'wpm2aws-key-' . $userName;
        
        try {
            $keyPair = $lightsailClient->createKeyPair(
                [
                    'keyPairName' => $keypairname, // '<string>', // REQUIRED
                    'tags' => [
                        [
                            'key' => 'create-origin', // '<string>',
                            'value' => 'wpm2aws', // '<string>',
                        ],
                        [
                            'key' => 'user-origin', //'<string>',
                            'value' => $userName, // '<string>',
                        ],
                    ],
                ]
            );

            $keyPairDetails = array(
                'name' => $keyPair['keyPair']['name'],
                'prkey' => $keyPair['privateKeyBase64'],
            );
            wpm2awsAddUpdateOptions('wpm2aws_lightsail_ssh', $keyPairDetails);
        
            return $keyPair['keyPair'];
        } catch (ApiException $e) {
            wpm2awsLogRAction('wpm2aws_create_key_pair_fail', 'AWS Error: ' . $e->getMessage());
            return false;
        } catch (LightsailException $e) {
            wpm2awsLogRAction('wpm2aws_create_key_pair_fail', 'AWS Error: ' . $e->getMessage());
            return false;
        } catch (Exception $e) {
            wpm2awsLogRAction('wpm2aws_create_key_pair_fail', 'AWS Error: ' . $e->getMessage());
            return false;
        }
    }

    private function launchLightsail($lightsailClient, $instanceName, $availabilityZoneId, $amiBlueprintId, $instanceBundleId, $copyDataScript, $keyPairName = null)
    {
        $userTag = get_option('wpm2aws-iam-user');

        if (
            false !==  get_option('wpm2aws_valid_licence_type') &&
            'TRIAL' === strtoupper(get_option('wpm2aws_valid_licence_type'))
        ) {
            $userRef = str_replace('@', '-', get_option('wpm2aws_licence_email'));
            $userRef = str_replace('.', '-', $userRef);
            
            $userTag .= '-' . $userRef;
        }

        $lightsailParams = array(
            'addOns' => [
                [
                    'addOnType' => 'AutoSnapshot', // REQUIRED
                    'autoSnapshotAddOnRequest' => [
                        'snapshotTimeOfDay' => '02:00',
                    ],
                ],
            ],
            'availabilityZone' => $availabilityZoneId,
            'blueprintId' => $amiBlueprintId,
            'bundleId' => $instanceBundleId,
            'instanceNames' => array(
                $instanceName
            ),
            'tags' => array(
                array(
                    'key' => 'create-origin',
                    'value' => 'wpm2aws'
                ),
                array(
                    'key' => 'user-origin',
                    'value' => $userTag,
                )
            ),
            'userData' => $copyDataScript
        );

        if (null !== $keyPairName && false !== $keyPairName) {
            $lightsailParams['keyPairName'] = $keyPairName;
        }



        // wp_die(print_r($lightsailParams));

        try {
            $lightsailInstance = $lightsailClient->createInstances($lightsailParams);
            // $lightsailInstance = $lightsailClient->createInstances(
            //     [
            //         'addOns' => [
            //             [
            //                 'addOnType' => 'AutoSnapshot', // REQUIRED
            //                 'autoSnapshotAddOnRequest' => [
            //                     'snapshotTimeOfDay' => '02:00',
            //                 ],
            //             ],
            //             // ...
            //         ],
            //         'availabilityZone' => $availabilityZoneId, // REQUIRED
            //         'blueprintId' => $amiBlueprintId, // REQUIRED
            //         'bundleId' => $instanceBundleId, // REQUIRED
            //         // 'customImageName' => '<string>',
            //         'instanceNames' => [$instanceName], // REQUIRED
            //         // 'keyPairName' => '<string>',
            //         'tags' => [
            //             [
            //                 'key' => 'create-origin',
            //                 'value' => 'wpm2aws',
            //             ],
            //             [
            //                 'key' => 'user-origin',
            //                 'value' => $userTag,
            //             ],
            //             // ...
            //         ],
            //         'userData' => $copyDataScript,
            //     ]
            // );
            // 'userData' => base64_encode($copyDataScript),
            $instanceDetails = $lightsailInstance->get('operations')[0];
            $instanceID = $instanceDetails['id'];
            $instanceStatus = $instanceDetails['status'];

            return $instanceStatus;

            // wp_die(print_r($lightsailInstance));
            // wp_die(print_r($operations));
            // $instanceID =$lightsailInstance;
            // $url = $lightsailInstance['operations']->get('effectiveUri');
            // wp_die("<strong>Successful API Call!</strong><br><br>Create LightSail.<br><br>AWS Instance ID:<br>" . $instanceID . '<br>Instance Status: ' . $instanceStatus . '<br>Region: ' . $this->credentials['region'] . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
            // 'Instance Address:<br><a href="' . $instanceStatus . '" target="_blank">' . $url . '</a>
        } catch (ApiException $e) {
            wpm2awsLogRAction('wpm2aws_create_lightsail_fail', 'AWS Error: ' . $e->getMessage());
            wp_die("<strong>API Call Failed! (launchAWS - api 1)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (LightsailException $e) {
            wpm2awsLogRAction('wpm2aws_create_lightsail_fail', 'AWS Error: ' . $e->getMessage());
            wp_die("<strong>API Call Failed! (launchAWS - AWS 1)</strong><br><br>Error Mgs: " . $e->get() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Exception $e) {
            wpm2awsLogRAction('wpm2aws_create_lightsail_fail', 'AWS Error: ' . $e->getMessage());
            wp_die("<strong>API Call Failed! (launchAWS - php 1)</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>' . $keyPairName);
        }
    }

    private function getLightsailBluePrints($lightsailClient)
    {
        try {
            $bluePrints = $lightsailClient->getBlueprints(
                [
                    'includeInactive' => false,
                    // 'pageToken' => '<string>',
                ]
            );
        } catch (ApiException $e) {
            wp_die("<strong>API Call Failed! (getLightsailBluePrints - api 1)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (LightsailException $e) {
            wp_die("<strong>API Call Failed! (AWS BluePrints)</strong><br><br>Error Mgs: " . $e->get() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Exception $e) {
            wp_die("<strong>API Call Failed! (getLightsailBluePrints - php 1)</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }
        // wp_die(print_r($bluePrints));
        if (!empty($bluePrints['blueprints'])) {
            foreach ($bluePrints['blueprints'] as $blueprint) {
                if ($blueprint['blueprintId'] === 'wordpress') {
                    return $bluePrint = array(
                        'bpid' => $blueprint['blueprintId'],
                        'version' => $blueprint['version']
                    );
                }
            }
        }

        wp_die('No WordPress AMI Available');
    }

    private function getAvailabilityZone($regionDetails, $preferredZone = null)
    {
        if (empty($regionDetails['azs'])) {
            return $regionDetails['region'];
        }

        if ($preferredZone) {
            foreach ($regionDetails['azs'] as $zone) {
                if (($zone['zoneName'] === $preferredZone) && ($zone['state'] === 'available')) {
                    return $zone['zoneName'];
                }
            }
        }

        foreach ($regionDetails['azs'] as $zone) {
            if ($zone['state'] === 'available') {
                return $zone['zoneName'];
            }
        }
    }

    private function getLightsailBundles($lightsailClient)
    {
        try {
            $bundles = $lightsailClient->getBundles(
                [
                    'includeInactive' => false,
                    // 'pageToken' => '<string>',
                ]
            );
        } catch (ApiException $e) {
            wp_die("<strong>API Call Failed! (getLightsailBundles - api 1)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (LightsailException $e) {
            wp_die("<strong>API Call Failed! (AWS Bundles)</strong><br><br>Error Mgs: " . $e->get() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Exception $e) {
            wp_die("<strong>API Call Failed! (getLightsailBundles - php 1)</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }
        // wp_die(print_r($bundles));
        // if (!empty($bluePrints['blueprints'])) {
        //     foreach ($bluePrints['blueprints'] as $blueprint) {
        //         if ($blueprint['blueprintId'] === 'wordpress') {
        //             return $bluePrint = array(
        //                 'bpid' => $blueprint['blueprintId'],
        //                 'version' => $blueprint['version']
        //             );
        //         }
        //     }
        // }
        return $bundles;
        wp_die('No AWS Bundles Available');
    }

    private function getBundle($bundleDetails, $preferredOs = null, $preferredBundle = null)
    {
        if (empty($bundleDetails['bundles'])) {
            return false;
        }

        // if ($preferredBundle) {
        //     foreach ($regionDetails['azs'] as $zone) {
        //         if (($zone['zoneName'] === $preferredZone) && ($zone['state'] === 'available')) {
        //             return $zone['zoneName'];
        //         }
        //     }
        // }

        $linuxBundles = $this->getLinuxBundles($bundleDetails);
        if (!$linuxBundles) {
            wp_die('No Linux Bundles Returned');
        }

        $cheapestBundle = $this->getBundleByCost($linuxBundles);
        if (!$cheapestBundle) {
            wp_die('No Specific Linux Bundle Returned');
        }
        return $cheapestBundle;
    }

    private function getLinuxBundles($bundleDetails)
    {
        if (empty($bundleDetails['bundles'])) {
            return false;
        }
        // wp_die(print_r($bundleDetails['bundles'][0]));
        foreach ($bundleDetails['bundles'] as $bundleIx => $bundle) {
            if (isset($bundle['supportedPlatforms'])) {
                if (!in_array('LINUX_UNIX', $bundle['supportedPlatforms'])) {
                    unset($bundleDetails['bundles'][$bundleIx]);
                }
            }
        }
        return $bundleDetails['bundles'];
    }


    private function getBundleByCost($bundles)
    {
        $lowestIndex = null;
        $pricing = array();
        foreach ($bundles as $bundleIx => $bundle) {
            if ($bundle['isActive']) {
                $pricing[$bundleIx] = $bundle['price'];

                // if ($lowestIndex === null) {
                //     $lowestIndex = $bundleIx;
                // } else {
                //     if ($bundle['price'] < $bundles[$lowestIndex]['price']) {
                //         $lowestIndex = $bundleIx;
                //     }
                // }
            }
        }
        if (!empty($pricing)) {
            $lowestPriceIndex = array_keys($pricing, min($pricing));
            if (!empty($lowestPriceIndex)) {
                return $bundles[$lowestPriceIndex[0]];
            }
            wp_die('No Linux Bundles Price Index Available');
        }

        wp_die('No Linux Bundles Available');

        // if (!empty($lowestIndex)) {
        //     return $bundles[$lowestIndex];
        // } else {
        //     wp_die('No Linux Bundles Available');
        // }
    }

    private function getInstanceDetails($lightsailClient, $instanceName, $detailKey = null)
    {
        try {
            $instance = $lightsailClient->getInstance(
                [
                    'instanceName' => $instanceName, // REQUIRED
                ]
            );
        } catch (ApiException $e) {
            wp_die("<strong>API Call Failed! (getInstanceDetails - api 1)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (LightsailException $e) {
            wp_die("<strong>API Call Failed! (AWS Instance Details)</strong><br><br>Error Mgs: " . $e->get() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Exception $e) {
            wp_die("<strong>API Call Failed! (getInstanceDetails - php 1)</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }

        if ($detailKey) {
            if (!empty($instance['instance'][$detailKey])) {
                return $key = $instance['instance'][$detailKey];
            }
        }
        return $instance['instance'];
    }

    private function getInstanceAccessDetails($lightsailClient, $instanceName, $detailKey = null)
    {
        try {
            $instance = $lightsailClient->getInstanceAccessDetails(
                [
                    'instanceName' => $instanceName, // REQUIRED
                ]
            );
        } catch (ApiException $e) {
            wp_die("<strong>API Call Failed! (getInstanceAccessDetails - api 1)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (LightsailException $e) {
            wp_die("<strong>API Call Failed! (AWS Instance Access Details)</strong><br><br>Error Mgs: " . $e->get() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Exception $e) {
            wp_die("<strong>API Call Failed! (getInstanceAccessDetails - php 1)</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }


        if ($detailKey) {
            if (!empty($instance['accessDetails'][$detailKey])) {
                return $key = $instance['accessDetails'][$detailKey];
            }
        }
        return $instance['accessDetails'];
    }

    private function getInstanceState($lightsailClient, $instanceName)
    {
        try {
            $instance = $lightsailClient->getInstanceState(
                [
                    'instanceName' => $instanceName, // REQUIRED
                ]
            );
        } catch (ApiException $e) {
            wp_die("<strong>API Call Failed! (getInstanceState - api 1)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (LightsailException $e) {
            wp_die("<strong>API Call Failed! (AWS Instance Access Details)</strong><br><br>Error Mgs: " . $e->get() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Exception $e) {
            wp_die("<strong>API Call Failed! (getInstanceState - php 1)</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }

        return $instance['state']['name'];
    }

    // public function getLightsailConsoleData($instanceDetails)
    // {
    //     if (false === get_option('wpm2aws-aws-lightsail-name') || '' === get_option('wpm2aws-aws-lightsail-name')) {
    //         wp_die('Error! INstance Name Can Not be Determined<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
    //     }
    //     $instanceName = get_option('wpm2aws-aws-lightsail-name');

    //     try {
    //         $lightsailClient = new LightsailClient($this->credentials);
    //     } catch (ApiException $e) {
    //         wp_die("<strong>API Call Failed! (api - getLightsailConsoleData=>Create AWS Client)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
    //     } catch (Exception $e) {
    //         wp_die("<strong>API Call Failed! (php - getLightsailConsoleData=>Create AWS Client)</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
    //     }

    //     try {
    //         $cloudTrailCredentials = $this->credentials;
    //         $cloudTrailCredentials['version'] = '2013-11-01';
    //         // unset($cloudTrailCredentials['version'] );
    //         // unset($cloudTrailCredentials['region'] );
    //         $cloudTrailClient = new CloudTrailClient($cloudTrailCredentials);
    //         // $cloudTrailClient = $cloudTrailClient->factory($cloudTrailCredentials);
    //     } catch (ApiException $e) {
    //         wpm2awsLogAction($e->getAwsErrorCode());
    //         // wp_die("<strong>API Call Failed! (api - getLightsailConsoleData=>Create CloudTrail Client)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
    //     } catch (CloudTrailException $e) {
    //         wpm2awsLogAction($e->getMessage());
    //         // wp_die("<strong>API Call Failed! (cloud trail - getLightsailConsoleData=>Create CloudTrail Client)</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
    //     } catch (Exception $e) {
    //         // wp_die(print_r($e));
    //         wpm2awsLogAction($e->getMessage());
    //         // wp_die("<strong>API Call Failed! (php ct - getLightsailConsoleData=>Create CloudTrail Client)</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
    //     }

    //     $instanceDetails = $this->getInstanceDetails($lightsailClient, $instanceName);

    //     $storageDetails = $this->getInstanceDisk($lightsailClient);

    //     $usageMetrics = $this->getInstanceMetrics($lightsailClient, $instanceName);

    //     $bundleDetails = $this->getInstanceBundleDetails($lightsailClient);

    //     $alarmDetails = $this->getInstanceAlarms($lightsailClient, $instanceName);


    //     $allSnapshotsDetails = $this->getInstanceSnapshots($lightsailClient);
    //     if (!empty($allSnapshotsDetails)) {
    //         $snapshotDetails = $this->parseSnaphotsThisInstance($allSnapshotsDetails);
    //     } else {
    //         $snapshotDetails = array();
    //     }


    //     $staticIpDetails = array();
    //     try {
    //         $staticIpDetails = $this->getAttachedStaticIp($lightsailClient);
    //     } catch (ApiException $e) {
    //         wpm2awsLogAction($e->getAwsErrorCode());
    //     } catch (LightsailException $e) {
    //         wpm2awsLogAction($e->getMessage());
    //     } catch (Exception $e) {
    //         wpm2awsLogAction($e->getMessage());
    //     }

    //     $cloudTrailDetails = array(
    //         'stopInstance' => array(),
    //         'startInstance' => array(),
    //         'putAlarms' => array(),
    //     );

    //     if (empty($cloudTrailClient)) {
    //         $cloudTrailDetails = array();
    //     } else {
    //         // $cloudTrailDetails = $this->getInstanceCloudTrail($cloudTrailClient, 'StartInstance');
    //         $cloudTrailDetails['stopInstance'] = $this->getInstanceCloudTrailEvents($cloudTrailClient, 'StopInstance', $instanceDetails['name']);
    //         $cloudTrailDetails['startInstance'] = $this->getInstanceCloudTrailEvents($cloudTrailClient, 'StartInstance', $instanceDetails['name']);
    //         $cloudTrailDetails['putAlarms'] = $this->getInstanceCloudTrailEvents($cloudTrailClient, 'PutAlarm', $instanceDetails['name']);
    //         // $instanceDetails['arn']
    //     }


    //     $data = array(
    //         'instance-details' => $instanceDetails,
    //         'static-ip-details' => $staticIpDetails, //
    //         'storage-details' => $storageDetails, //
    //         'usage-metrics' => $usageMetrics, //
    //         'bundle-details' => $bundleDetails, //
    //         'alarm-details' => $alarmDetails, //
    //         'cloud-trail-details' => $cloudTrailDetails, //
    //         'manual-snapshot-details' => $snapshotDetails, //
    //     );

    //     return $data;
    // }

    public function getRemoteConsoleData()
    {
        // if (false === get_option('wpm2aws-lightsail-instance-details')) {
        //     return json_encode(array('error' => 'Invalid Request Parameters (1)', 'code' => '401'));
        // }

        if (false === get_option('wpm2aws-iampw')) {
            return array('error' => 'Invalid Request Parameters (2)', 'code' => '401');
        }

        if (false === get_option('wpm2aws-iamid')) {
            return array('error' => 'Invalid Request Parameters (3)', 'code' => '401');
        }

        if (empty($_SERVER['SERVER_ADDR'])) {
            return array('error' => 'Invalid Request Parameters (4)', 'code' => '401');
        }

        $key = get_option('wpm2aws-iamid');
        $secret = get_option('wpm2aws-iampw');

        if (
            false !==  get_option('wpm2aws_valid_licence_type') &&
            'TRIAL' === strtoupper(get_option('wpm2aws_valid_licence_type'))
        ) {
            $key = str_replace(get_option('wpm2aws_valid_licence_dyck'), '', base64_decode(get_option('wpm2aws-iamid')));
            $secret = str_replace(get_option('wpm2aws_valid_licence_dyck'), '', base64_decode(get_option('wpm2aws-iampw')));
        }

        $requestData = array(
            'wpm2aws-key' => $key,
            'wpm2aws-token' => $secret,
            'wpm2aws-user-email' => get_option('wpm2aws_licence_email'),
            // 'wpm2aws-instancearn' => get_option('wpm2aws-lightsail-instance-details')['details']['arn'],
            'wpm2aws-instancename' => get_option('wpm2aws-aws-lightsail-name'),
            'wpm2aws-serveraddress' => (!empty($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : ''),
        );
        // wp_die(print_r($instanceDetails));
        // $response = wp_remote_post( 'http://localhost/wpm2aws-console-api/public/api/console/data', array(
        $response = wp_remote_post(
            'http://52.213.129.222/api/console/data',
            array(

            'method' => 'POST',
            'timeout' => 45,
            'redirection' => 10,
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => array(
                'Cache-Control' => 'no-cache',
            ),
            'body' => array( 'data' => $requestData ),
            'cookies' => array(),
            )
        );
        // return $response;
        $responseCode = wp_remote_retrieve_response_code($response);
        // wp_die(print_r($response));
        if ('200' !== $responseCode && 200 !== $responseCode) {
            $errorMessage = json_decode($response['body'], true);
            if ((int)$responseCode >= 400 && (int)$responseCode < 500) {
                return array('error' => 'Unauthorised Access<br>' . $errorMessage, 'code' => $responseCode);
            } else {
                return array('error' => 'An Error Has Occurred (data)<br>' . $errorMessage, 'code' => $responseCode);
            }
        }

        if (!isset($response['body'])) {
            return $data = array();
        }

        // wp_die(print_r($response));
        $remoteData = json_decode($response['body'], true);


        if (!isset($remoteData['remote-data'])) {
            return $data = array();
        }
        
        return $remoteData['remote-data'];
    }



    private function getInstanceDisk($lightsailClient)
    {
        try {
            $instanceDisk = $lightsailClient->getDisks();
            // $instanceDisk = $lightsailClient->getDisk(
            //     [
            //         'diskName' => $diskName, // REQUIRED
            //     ]
            // );
        } catch (ApiException $e) {
            wp_die("<strong>API Call Failed! (getInstanceDisk - api 1)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (LightsailException $e) {
            wp_die("<strong>API Call Failed! (getInstanceDisk - Lightsail)</strong><br><br>Error Mgs: " . $e->get() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Exception $e) {
            wp_die("<strong>API Call Failed! (getInstanceDisk - php)</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }

        return $instanceDisk['disks'];
    }

    private function getInstanceMetrics($lightsailClient, $instanceName)
    {
        // 'metricName' => 'CPUUtilization|NetworkIn|NetworkOut|StatusCheckFailed|StatusCheckFailed_Instance|StatusCheckFailed_System', // REQUIRED
        // 'unit' => 'Seconds|Microseconds|Milliseconds|Bytes|Kilobytes|Megabytes|Gigabytes|Terabytes|Bits|Kilobits|Megabits|Gigabits|Terabits|Percent|Count|Bytes/Second|Kilobytes/Second|Megabytes/Second|Gigabytes/Second|Terabytes/Second|Bits/Second|Kilobits/Second|Megabits/Second|Gigabits/Second|Terabits/Second|Count/Second|None', // REQUIRED

        try {
            $instanceMetrics = $lightsailClient->getInstanceMetricData([
                'endTime' => time(), // REQUIRED
                'instanceName' => $instanceName, // REQUIRED
                'metricName' => 'CPUUtilization', // REQUIRED
                'period' => 600, // REQUIRED
                'startTime' => date("Y/m/d"), // REQUIRED
                'statistics' => ['Minimum', 'Maximum', 'Sum', 'Average'], // REQUIRED
                'unit' => 'Percent', // REQUIRED
            ]);
        } catch (ApiException $e) {
            wp_die("<strong>API Call Failed! (getInstanceMetrics - api 1)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (LightsailException $e) {
            wp_die("<strong>API Call Failed! (getInstanceMetrics - Lightsail)</strong><br><br>Error Mgs: " . $e->get() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Exception $e) {
            wp_die("<strong>API Call Failed! (getInstanceMetrics - php)</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }

        return $instanceMetrics['metricData'];
    }

    private function getInstanceBundleDetails($lightsailClient)
    {
        try {
            $bundleDetails = $lightsailClient->getBundles();
        } catch (ApiException $e) {
            wp_die("<strong>API Call Failed! (getInstanceBundleDetails - api 1)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (LightsailException $e) {
            wp_die("<strong>API Call Failed! (getInstanceBundleDetails - Lightsail)</strong><br><br>Error Mgs: " . $e->get() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Exception $e) {
            wp_die("<strong>API Call Failed! (getInstanceBundleDetails - php)</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }

        return $bundleDetails['bundles'];
    }


    private function getInstanceAlarms($lightsailClient, $instanceName)
    {


        // $alarmDetails = $lightsailClient->getAlarms(
        //     [
        //         'monitoredResourceName' => $instanceName
        //     ]
        // );
        try {
            $alarmDetails = $lightsailClient->getAlarms(
                [
                    'monitoredResourceName' => $instanceName
                ]
            );
        } catch (ApiException $e) {
            wp_die("<strong>API Call Failed! (getInstanceAlarms - api 1)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (LightsailException $e) {
            wp_die("<strong>API Call Failed! (getInstanceAlarms - Lightsail)</strong><br><br>Error Mgs: " . $e->get() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Exception $e) {
            wp_die("<strong>API Call Failed! (getInstanceAlarms - php)</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }

        return $alarmDetails['alarms'];
    }


    // This returns all snapshots in account - do not use
    private function getInstanceSnapshots($lightsailClient)
    {
        try {
            $snapshotDetails = $lightsailClient->getInstanceSnapshots();
        } catch (ApiException $e) {
            wp_die("<strong>API Call Failed! (getInstanceSnapshots - api 1)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (LightsailException $e) {
            wp_die("<strong>API Call Failed! (getInstanceSnapshots - Lightsail)</strong><br><br>Error Mgs: " . $e->get() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Exception $e) {
            wp_die("<strong>API Call Failed! (getInstanceSnapshots - php)</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }

        return $snapshotDetails['instanceSnapshots'];
    }



    private function parseSnaphotsThisInstance($allSnapshots)
    {
        $snapshots = array();

        if (empty($allSnapshots)) {
            return $snapshots;
        }

        if (false === get_option('wpm2aws-aws-lightsail-name') || '' === get_option('wpm2aws-aws-lightsail-name')) {
            return $snapshots;
        }

        $instanceName = get_option('wpm2aws-aws-lightsail-name');

        foreach ($allSnapshots as $snapsIx => $snapsVal) {
            if ($snapsVal['fromInstanceName'] === $instanceName) {
                array_push($snapshots, $snapsVal);
            }
        }
        return $snapshots;
    }


    private function getSnapshotByName($lightsailClient, $snapshotName)
    {
        try {
            $snapshotDetails = $lightsailClient->getInstanceSnapshot(
                [
                    'instanceSnapshotName' => $snapshotName // '<string>', // REQUIRED
                ]
            );
        } catch (ApiException $e) {
            wp_die("<strong>API Call Failed! (getSnapshotByName - api 1)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (LightsailException $e) {
            wp_die("<strong>API Call Failed! (getSnapshotByName - Lightsail)</strong><br><br>Error Mgs: " . $e->get() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Exception $e) {
            wp_die("<strong>API Call Failed! (getSnapshotByName - php)</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }

        return $snapshotDetails['instanceSnapshot'];
    }

    private function getInstanceCloudTrail($cloudTrailClient, $instanceArnName)
    {
        try {
            $cloudTrailDetails = $cloudTrailClient->getTrail([
                'Name' => $instanceArnName, // REQUIRED
            ]);
        } catch (ApiException $e) {
            wp_die("<strong>API Call Failed! (getInstanceAlarms - api 1)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (CloudTrailException $e) {
            wp_die("<strong>API Call Failed! (getInstanceAlarms - Lightsail)</strong><br><br>Error Mgs: " . $e->get() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Exception $e) {
            wp_die("<strong>API Call Failed! (getInstanceAlarms - php)</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }

        return $cloudTrailDetails['Trail'];
    }

    private function getInstanceCloudTrailEvents($cloudTrailClient, $eventName, $instanceName)
    {
        try {
            // $cloudTrailEventsDetails = $cloudTrailClient->lookupEvents();
            $cloudTrailEventsDetails = $cloudTrailClient->lookupEvents([
                'LookupAttributes' => [
                    // [
                    //     'AttributeKey' => 'EventSource', // REQUIRED
                    //     'AttributeValue' => 'lightsail.amazonaws.com', // REQUIRED
                    // ],
                    [
                        'AttributeKey' => 'EventName', // REQUIRED
                        'AttributeValue' => $eventName, // REQUIRED
                    ],
                    // [
                    //     'AttributeKey' => 'EventName', // REQUIRED
                    //     'AttributeValue' => 'StartInstances', // REQUIRED
                    // ],
                    // [
                    //     'AttributeKey' => 'ResourceName', // REQUIRED
                    //     'AttributeValue' => $instanceName, // REQUIRED
                    // ],


                    // ...
                ],
                // 'StartTime' => '2020-03-30T00:00:01+00:00',
                // 'EndTime' => '2020-03-30T23:59:59+00:00',
            ]);
        } catch (ApiException $e) {
            wp_die("<strong>API Call Failed! (getInstanceCloudTrailEvents - api 1)</strong><br><br>Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (CloudTrailException $e) {
            wp_die("<strong>API Call Failed! (getInstanceCloudTrailEvents - Lightsail)</strong><br><br>Error Mgs: " . $e->get() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        } catch (Exception $e) {
            wp_die("<strong>API Call Failed! (getInstanceCloudTrailEvents - php)</strong><br><br>Error Mgs: " . $e->getMessage() . '<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }

        $lightsailEvents = array();
        // if (!empty($cloudTrailEventsDetails['Events'])) {
        //     foreach ($cloudTrailEventsDetails['Events'] as $eventIx => $eventDetails) {
        //         if ('lightsail.amazonaws.com' === $eventDetails['EventSource']) {
        //             array_push($lightsailEvents, $eventDetails);
        //         }
        //     }
        // }
        // return $lightsailEvents;


        if (!empty($cloudTrailEventsDetails['Events'])) {
            foreach ($cloudTrailEventsDetails['Events'] as $eventIx => $eventDetails) {
                // if (!in_array($eventDetails['EventName'], WPM2AWS_CONSOLE_LOG_EVENTS)) {
                //     break;
                // }
                if ('lightsail.amazonaws.com' !== $eventDetails['EventSource']) {
                    break;
                }
                $eventTrail = json_decode($eventDetails['CloudTrailEvent']);
                // $eventTrail = $eventDetails['CloudTrailEvent'];
                // array_push($lightsailEvents, $eventTrail->responseElements->operations[0]->resourceName);
                // array_push($lightsailEvents, $eventTrail->responseElements->operations);


                $isThisResource = false;
                if (!empty($eventTrail) && !empty($eventTrail->responseElements) && !empty($eventTrail->responseElements->operations)) {
                    foreach ($eventTrail->responseElements->operations as $operationIx => $OperationVals) {
                        if ($instanceName === $eventTrail->responseElements->operations[$operationIx]->resourceName) {
                            $isThisResource = true;
                        }
                    }
                }


                if (true === $isThisResource) {
                    array_push($lightsailEvents, $cloudTrailEventsDetails['Events'][$eventIx]);
                }

                // if ($instanceName !== $eventTrail->responseElements->operations[0]->resourceName) {
                //     break;
                // }
                // array_push($lightsailEvents, $cloudTrailEventsDetails['Events'][$eventIx]);


                // if ('StopInstance' !== $eventDetails['EventName']) {
                //     array_push($lightsailEvents, $cloudTrailEventsDetails['Events'][$eventIx]);
                // }
                // if ('lightsail.amazonaws.com' === $eventDetails['EventSource']) {
                //     array_push($lightsailEvents, $eventDetails);
                // }
            }
        }
        return $lightsailEvents;

        return $cloudTrailEventsDetails['Events'];
    }

    public function backgroundZipFileToLocal($basePath, $fileOrDir, $downloadFileOrDir)
    {
        return 'test - backgroundZipFileToLocal';
    }

    public function zipDirectoryAndDownload($basePath, $fileName)
    {
        if (empty($fileName)) {
            wpm2awsLogAction('Full Zip Error: No File Name Given');
            return false;
        }

        $pathSeparator = DIRECTORY_SEPARATOR;

        $dirPath = $basePath . $pathSeparator . $fileName;
        $zippedFilePath = '';

        $pathSeparator = DIRECTORY_SEPARATOR;
        if (!is_dir($dirPath)) {
            wpm2awsLogAction('Full Zip Error: Invalid Path');
            return false;
        }
        if (DIRECTORY_SEPARATOR !== substr($basePath, -1)) {
            $basePath .= DIRECTORY_SEPARATOR;
        }

        try {
            $zippedFilePath = $this->zipFullDirectory($basePath, $fileName, $pathSeparator, true);
            
            if (defined('WPM2AWS_TESTING') || defined('WPM2AWS_DEBUG') || defined('WPM2AWS_DEV')) {
                wpm2awsLogAction('Full Zip PAth: ' . $zippedFilePath);
            }
            
            return '200';
        } catch (Exception $e) {
            wpm2awsLogAction('Full Zip Error: ' . $e->getMessage());
            return false;
        }

        if ('' === $zippedFilePath) {
            wpm2awsLogAction('Full Zip Error - Incomplete');
            return $status = '404';
            // return false;
        }

        $restricted = true;
        if (false !== get_option('wpm2aws-customer-type') && 'self' === get_option('wpm2aws-customer-type')) {
            $restricted = false;
        }

        // try {
        //     $transferred = $this->uploadZippedFileToBucket($zippedFilePath . '.zip', $restricted);
        //     if (defined('WPM2AWS_TESTING') || defined('WPM2AWS_DEBUG') || defined('WPM2AWS_DEV')) {
        //         wpm2awsLogAction('Full Zip: ' . $transferred);
        //     }
        //     return $transferred;
        // } catch (Exception $e) {
        //     wpm2awsLogAction('Full Zip Error: ' . $e->getMessage());
        //     return false;
        // }
    }

    // public function addNewMetricAlarm($alarmName, $metric, $comparitor, $triggerPoint, $threshold)
    // {




    //     if (false === get_option('wpm2aws-aws-lightsail-name') || '' === get_option('wpm2aws-aws-lightsail-name')) {
    //         wp_die('Error! Instance Name Can Not be Determined<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
    //     }
    //     $instanceName = get_option('wpm2aws-aws-lightsail-name');


    //     $test = 'AlarmName: ' . $alarmName . ' | ' . gettype($alarmName);
    //     $test .= '<br>Comparitor: ' . $comparitor . ' | ' . gettype($comparitor);
    //     $test .= '<br>TriggerPoint: ' . $triggerPoint . ' | ' . gettype($triggerPoint);
    //     $test .= '<br>Metric: ' . $metric . ' | ' . gettype($metric);
    //     $test .= '<br>instanceName: ' . $instanceName . ' | ' . gettype($instanceName);
    //     $test .= '<br>Threshold: ' . $threshold . ' | ' . gettype($threshold);
    //     // wp_die($test);

    // try {
    //     $lightsailClient = new LightsailClient($this->credentials);
    // } catch (ApiException $e) {
    //     $msg = "Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage();
    //     set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
    //     throw new Exception($msg);
    //     // return false;
    //  } catch (Exception $e) {
    //     $msg = "Error Mgs: <br><strong>" . $e->getMessage() . '</strong>';
    //     set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
    //     throw new Exception($msg);
    //     // return false;
    // }


    // try {
    //     $newAlarm = $lightsailClient->putAlarm(
    //         [
    //             'alarmName' => $alarmName, // REQUIRED '<string>'
    //             'comparisonOperator' => $comparitor, // REQUIRED 'GreaterThanOrEqualToThreshold|GreaterThanThreshold|LessThanThreshold|LessThanOrEqualToThreshold'
    //             // 'contactProtocols' => ['<string>', ...],
    //             // 'datapointsToAlarm' => <integer>,
    //             'evaluationPeriods' => $triggerPoint, // REQUIRED <integer>
    //             'metricName' => $metric, // REQUIRED  - Default for now 'CPUUtilization|NetworkIn|NetworkOut|StatusCheckFailed|StatusCheckFailed_Instance|StatusCheckFailed_System|ClientTLSNegotiationErrorCount|HealthyHostCount|UnhealthyHostCount|HTTPCode_LB_4XX_Count|HTTPCode_LB_5XX_Count|HTTPCode_Instance_2XX_Count|HTTPCode_Instance_3XX_Count|HTTPCode_Instance_4XX_Count|HTTPCode_Instance_5XX_Count|InstanceResponseTime|RejectedConnectionCount|RequestCount|DatabaseConnections|DiskQueueDepth|FreeStorageSpace|NetworkReceiveThroughput|NetworkTransmitThroughput'
    //             'monitoredResourceName' => $instanceName, // REQUIRED '<string>'
    //             // 'notificationEnabled' => true || false, - defautls to true
    //             // 'notificationTriggers' => ['<string>', ...], - defaults to ALARM
    //             'threshold' => (float)$threshold, // REQUIRED <float>
    //             // 'treatMissingData' => 'breaching|notBreaching|ignore|missing', - defaults to MISSING
    //         ]
    //     );
    // } catch (ApiException $e) {
    //     $msg = "Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage();
    //     set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
    //     throw new Exception($msg);
    //     // return false;
    // } catch (LightsailException $e) {
    //     $msg = "Error Mgs: <br><strong>" . $e->get() . '</strong>';
    //     set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
    //     throw new Exception($msg);
    //     // return false;
    // } catch (Exception $e) {
    //     $msg = "Error Mgs: <br><strong>" . $e->getMessage() . '</strong>';
    //     set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
    //     throw new Exception($msg);
    //     // return false;
    // }

    // return $newAlarm['operations'];
    // }
    

    public function remoteAddNewMetricAlarm($alarmName, $metric, $comparitor, $triggerPoint, $threshold)
    {
        if (false === get_option('wpm2aws-iampw')) {
            return array('error' => 'Invalid Request Parameters (2)', 'code' => '401');
        }

        if (false === get_option('wpm2aws-iamid')) {
            return array('error' => 'Invalid Request Parameters (3)', 'code' => '401');
        }

        if (empty($_SERVER['SERVER_ADDR'])) {
            return array('error' => 'Invalid Request Parameters (4)', 'code' => '401');
        }

        $alarmData = array(
            'alarmname' => $alarmName,
            'alarmmetric' => $metric,
            'alarmcomparitor' => $comparitor,
            'alarmtriggerpoint' => $triggerPoint,
            'alarmthreshold' => $threshold,
        );

        $key = get_option('wpm2aws-iamid');
        $secret = get_option('wpm2aws-iampw');

        if (
            false !==  get_option('wpm2aws_valid_licence_type') &&
            'TRIAL' === strtoupper(get_option('wpm2aws_valid_licence_type'))
        ) {
            $key = str_replace(get_option('wpm2aws_valid_licence_dyck'), '', base64_decode(get_option('wpm2aws-iamid')));
            $secret = str_replace(get_option('wpm2aws_valid_licence_dyck'), '', base64_decode(get_option('wpm2aws-iampw')));
        }

        $requestData = array(
            'wpm2aws-key' => $key,
            'wpm2aws-token' => $secret,
            'wpm2aws-user-email' => get_option('wpm2aws_licence_email'),
            'wpm2aws-instancename' => get_option('wpm2aws-aws-lightsail-name'),
            'wpm2aws-serveraddress' => (!empty($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : ''),
            'wpm2aws-alarmdetails' => $alarmData,
        );

        // $response = wp_remote_post( 'http://localhost/wpm2aws-console-api/public/api/console/addAlarm', array(
        $response = wp_remote_post(
            'http://52.213.129.222/api/console/addAlarm',
            array(
            'method' => 'PUT',
            'timeout' => 45,
            'redirection' => 10,
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => array(
                'Cache-Control' => 'no-cache',
            ),
            'body' => array( 'data' => $requestData ),
            'cookies' => array(),
            )
        );
        // return $response;
        $responseCode = wp_remote_retrieve_response_code($response);
        // wp_die(print_r($response));
        if ('200' !== $responseCode && 200 !== $responseCode) {
            $errorMessage = json_decode($response['body'], true);
            if ((int)$responseCode >= 400 && (int)$responseCode < 500) {
                return array('error' => 'Unauthorised Access<br>' . $errorMessage, 'code' => $responseCode);
            } else {
                return array('error' => 'An Error Has Occurred<br>' . $errorMessage, 'code' => $responseCode);
            }
        }

        if (!isset($response['body'])) {
            return $data = array();
        }

        // wp_die(print_r($response));
        $remoteData = json_decode($response['body'], true);


        if (!isset($remoteData['remote-data'])) {
            return $data = array();
        }

        return $remoteData['remote-data'];
    }
    // public function rebootInstance()
    // {
    //     if (false === get_option('wpm2aws-aws-lightsail-name') || '' === get_option('wpm2aws-aws-lightsail-name')) {
    //         wp_die('Error! Instance Name Can Not be Determined<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
    //     }
    //     $instanceName = get_option('wpm2aws-aws-lightsail-name');

    //     try {
    //         $lightsailClient = new LightsailClient($this->credentials);
    //     } catch (ApiException $e) {
    //         $msg = "Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage();
    //         set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
    //         throw new Exception($msg);
    //      } catch (Exception $e) {
    //         $msg = "Error Mgs: <br><strong>" . $e->getMessage() . '</strong>';
    //         set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
    //         throw new Exception($msg);
    //     }


    //     try {
    //         $reboot = $lightsailClient->rebootInstance(
    //             [
    //                 'instanceName' => $instanceName, // REQUIRED '<string>'
    //             ]
    //         );
    //     } catch (ApiException $e) {
    //         $msg = "Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage();
    //         set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
    //         throw new Exception($msg);
    //     } catch (LightsailException $e) {
    //         $msg = "Error Mgs: <br><strong>" . $e->get() . '</strong>';
    //         set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
    //         throw new Exception($msg);
    //     } catch (Exception $e) {
    //         $msg = "Error Mgs: <br><strong>" . $e->getMessage() . '</strong>';
    //         set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
    //         throw new Exception($msg);
    //     }

    //     return $reboot['operations'];
    // }

    public function remoteRebootInstance()
    {
        // wp_die('reboot');
        // if (false === get_option('wpm2aws-lightsail-instance-details')) {
        //     return json_encode(array('error' => 'Invalid Request Parameters (1)', 'code' => '401'));
        // }

        if (false === get_option('wpm2aws-iampw')) {
            return array('error' => 'Invalid Request Parameters (2)', 'code' => '401');
        }

        if (false === get_option('wpm2aws-iamid')) {
            return array('error' => 'Invalid Request Parameters (3)', 'code' => '401');
        }

        if (empty($_SERVER['SERVER_ADDR'])) {
            return array('error' => 'Invalid Request Parameters (4)', 'code' => '401');
        }

        $key = get_option('wpm2aws-iamid');
        $secret = get_option('wpm2aws-iampw');

        if (
            false !==  get_option('wpm2aws_valid_licence_type') &&
            'TRIAL' === strtoupper(get_option('wpm2aws_valid_licence_type'))
        ) {
            $key = str_replace(get_option('wpm2aws_valid_licence_dyck'), '', base64_decode(get_option('wpm2aws-iamid')));
            $secret = str_replace(get_option('wpm2aws_valid_licence_dyck'), '', base64_decode(get_option('wpm2aws-iampw')));
        }

        $requestData = array(
            'wpm2aws-key' => $key,
            'wpm2aws-token' => $secret,
            'wpm2aws-user-email' => get_option('wpm2aws_licence_email'),
            // 'wpm2aws-instancearn' => get_option('wpm2aws-lightsail-instance-details')['details']['arn'],
            'wpm2aws-instancename' => get_option('wpm2aws-aws-lightsail-name'),
            'wpm2aws-serveraddress' => (!empty($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : ''),
        );
        // wp_die(print_r($instanceDetails));
        // $response = wp_remote_post( 'http://localhost/wpm2aws-console-api/public/api/console/rebootInstance', array(
        $response = wp_remote_post(
            'http://52.213.129.222/api/console/rebootInstance',
            array(

            'method' => 'POST',
            'timeout' => 45,
            'redirection' => 10,
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => array(
                'Cache-Control' => 'no-cache',
            ),
            'body' => array( 'data' => $requestData ),
            'cookies' => array(),
            )
        );
        // return $response;
        $responseCode = wp_remote_retrieve_response_code($response);
        // wp_die(print_r($response));
        if ('200' !== $responseCode && 200 !== $responseCode) {
            $errorMessage = json_decode($response['body'], true);
            if ((int)$responseCode >= 400 && (int)$responseCode < 500) {
                // wp_die(print_r($errorMessage));
                throw new Exception('Unauthorised Access<br>' . $errorMessage);
                return array('error' => 'Unauthorised Access<br>' . $errorMessage, 'code' => $responseCode);
            } else {
                // wp_die(print_r($errorMessage));
                throw new Exception('Unauthorised Access<br>' . $errorMessage);
                return array('error' => 'An Error Has Occurred<br>' . $errorMessage, 'code' => $responseCode);
            }
        }

        if (!isset($response['body'])) {
            return $data = array();
        }

        // wp_die(print_r($response));
        $remoteData = json_decode($response['body'], true);


        if (!isset($remoteData['remote-data'])) {
            return $data = array();
        }
        
        return $remoteData['remote-data'];
        
        
        
        
        // $instanceName = get_option('wpm2aws-aws-lightsail-name');

        // try {
        //     $lightsailClient = new LightsailClient($this->credentials);
        // } catch (ApiException $e) {
        //     $msg = "Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage();
        //     set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
        //     throw new Exception($msg);
        //  } catch (Exception $e) {
        //     $msg = "Error Mgs: <br><strong>" . $e->getMessage() . '</strong>';
        //     set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
        //     throw new Exception($msg);
        // }


        // try {
        //     $reboot = $lightsailClient->rebootInstance(
        //         [
        //             'instanceName' => $instanceName, // REQUIRED '<string>'
        //         ]
        //     );
        // } catch (ApiException $e) {
        //     $msg = "Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage();
        //     set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
        //     throw new Exception($msg);
        // } catch (LightsailException $e) {
        //     $msg = "Error Mgs: <br><strong>" . $e->get() . '</strong>';
        //     set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
        //     throw new Exception($msg);
        // } catch (Exception $e) {
        //     $msg = "Error Mgs: <br><strong>" . $e->getMessage() . '</strong>';
        //     set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
        //     throw new Exception($msg);
        // }

        // return $reboot['operations'];
    }


    // public function createManualSnapshot($snapShotName)
    // {
    //     if (false === get_option('wpm2aws-aws-lightsail-name') || '' === get_option('wpm2aws-aws-lightsail-name')) {
    //         wp_die('Error! Instance Name Can Not be Determined<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
    //     }
    //     if (false === get_option('wpm2aws-iam-user') || '' === get_option('wpm2aws-iam-user')) {
    //         wp_die('Error! IAM User Name Can Not be Determined<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
    //     }

    //     $instanceName = get_option('wpm2aws-aws-lightsail-name');
    //     $userName = get_option('wpm2aws-iam-user');

    //     try {
    //         $lightsailClient = new LightsailClient($this->credentials);
    //     } catch (ApiException $e) {
    //         $msg = "Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage();
    //         set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
    //         throw new Exception($msg);
    //      } catch (Exception $e) {
    //         $msg = "Error Mgs: <br><strong>" . $e->getMessage() . '</strong>';
    //         set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
    //         throw new Exception($msg);
    //     }


    //     try {
    //         $snapshot = $lightsailClient->createInstanceSnapshot(
    //             [
    //                 'instanceName' => $instanceName, // REQUIRED '<string>'
    //                 'instanceSnapshotName' => $snapShotName, // REQUIRED '<string>'
    //                 'tags' => [
    //                     [
    //                         'key' => 'create-origin',
    //                         'value' => 'wpm2aws',
    //                     ],
    //                     [
    //                         'key' => 'user-origin',
    //                         'value' => $userName,
    //                     ],
    //                 ],
    //             ]
    //         );
    //     } catch (ApiException $e) {
    //         $msg = "Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage();
    //         set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
    //         throw new Exception($msg);
    //     } catch (LightsailException $e) {
    //         $msg = "Error Mgs: <br><strong>" . $e->get() . '</strong>';
    //         set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
    //         throw new Exception($msg);
    //     } catch (Exception $e) {
    //         $msg = "Error Mgs: <br><strong>" . $e->getMessage() . '</strong>';
    //         set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
    //         throw new Exception($msg);
    //     }

    //     return $snapshot['operations'];
    // }


    public function remoteCreateManualSnapshot($snapShotName)
    {
        // wp_die('reboot');
        // if (false === get_option('wpm2aws-lightsail-instance-details')) {
        //     return array('error' => 'Invalid Request Parameters (1)', 'code' => '401');
        // }

        if (false === get_option('wpm2aws-iampw')) {
            return array('error' => 'Invalid Request Parameters (2)', 'code' => '401');
        }

        if (false === get_option('wpm2aws-iamid')) {
            return array('error' => 'Invalid Request Parameters (3)', 'code' => '401');
        }

        if (empty($_SERVER['SERVER_ADDR'])) {
            return array('error' => 'Invalid Request Parameters (4)', 'code' => '401');
        }
        
        if (empty($snapShotName)) {
            return array('error' => 'Invalid Request Parameters (5)', 'code' => '401');
        }

        $key = get_option('wpm2aws-iamid');
        $secret = get_option('wpm2aws-iampw');

        if (
            false !==  get_option('wpm2aws_valid_licence_type') &&
            'TRIAL' === strtoupper(get_option('wpm2aws_valid_licence_type'))
        ) {
            $key = str_replace(get_option('wpm2aws_valid_licence_dyck'), '', base64_decode(get_option('wpm2aws-iamid')));
            $secret = str_replace(get_option('wpm2aws_valid_licence_dyck'), '', base64_decode(get_option('wpm2aws-iampw')));
        }

        $requestData = array(
            'wpm2aws-key' => $key,
            'wpm2aws-token' => $secret,
            'wpm2aws-user-email' => get_option('wpm2aws_licence_email'),
            // 'wpm2aws-instancearn' => get_option('wpm2aws-lightsail-instance-details')['details']['arn'],
            'wpm2aws-instancename' => get_option('wpm2aws-aws-lightsail-name'),
            'wpm2aws-serveraddress' => (!empty($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : ''),
            'wpm2aws-snapshotname' => $snapShotName,
        );

        // wp_die(print_r($instanceDetails));
        // $response = wp_remote_post( 'http://localhost/wpm2aws-console-api/public/api/console/createSnapshot', array(
        $response = wp_remote_post(
            'http://52.213.129.222/api/console/createSnapshot',
            array(

            'method' => 'POST',
            'timeout' => 45,
            'redirection' => 10,
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => array(
                'Cache-Control' => 'no-cache',
            ),
            'body' => array( 'data' => $requestData ),
            'cookies' => array(),
            )
        );
        // return $response;
        $responseCode = wp_remote_retrieve_response_code($response);
        // wp_die(print_r($response));
        if ('200' !== $responseCode && 200 !== $responseCode) {
            $errorMessage = json_decode($response['body'], true);
            if ((int)$responseCode >= 400 && (int)$responseCode < 500) {
                // wp_die(print_r($errorMessage));
                throw new Exception('Unauthorised Access<br>' . $errorMessage);
                return array('error' => 'Unauthorised Access<br>' . $errorMessage, 'code' => $responseCode);
            } else {
                // wp_die(print_r($errorMessage));
                throw new Exception('Unauthorised Access<br>' . $errorMessage);
                return array('error' => 'An Error Has Occurred<br>' . $errorMessage, 'code' => $responseCode);
            }
        }

        if (!isset($response['body'])) {
            return $data = array();
        }

        // wp_die(print_r($response));
        $remoteData = json_decode($response['body'], true);


        if (!isset($remoteData['remote-data'])) {
            return $data = array();
        }
        
        return $remoteData['remote-data'];
        
        
        
        
        
        // ***
        // $instanceName = get_option('wpm2aws-aws-lightsail-name');
        // $userName = get_option('wpm2aws-iam-user');

        // try {
        //     $lightsailClient = new LightsailClient($this->credentials);
        // } catch (ApiException $e) {
        //     $msg = "Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage();
        //     set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
        //     throw new Exception($msg);
        //  } catch (Exception $e) {
        //     $msg = "Error Mgs: <br><strong>" . $e->getMessage() . '</strong>';
        //     set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
        //     throw new Exception($msg);
        // }


        // try {
        //     $snapshot = $lightsailClient->createInstanceSnapshot(
        //         [
        //             'instanceName' => $instanceName, // REQUIRED '<string>'
        //             'instanceSnapshotName' => $snapShotName, // REQUIRED '<string>'
        //             'tags' => [
        //                 [
        //                     'key' => 'create-origin',
        //                     'value' => 'wpm2aws',
        //                 ],
        //                 [
        //                     'key' => 'user-origin',
        //                     'value' => $userName,
        //                 ],
        //             ],
        //         ]
        //     );
        // } catch (ApiException $e) {
        //     $msg = "Error Mgs: <br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage();
        //     set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
        //     throw new Exception($msg);
        // } catch (LightsailException $e) {
        //     $msg = "Error Mgs: <br><strong>" . $e->get() . '</strong>';
        //     set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
        //     throw new Exception($msg);
        // } catch (Exception $e) {
        //     $msg = "Error Mgs: <br><strong>" . $e->getMessage() . '</strong>';
        //     set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
        //     throw new Exception($msg);
        // }

        // return $snapshot['operations'];
    }


    public function createInstanceFromSnapshot($lightsailClient, $snapshotDetails, $launchRegion = null, $launchPlanId = null)
    {
        if (false === get_option('wpm2aws-aws-lightsail-name') || '' === get_option('wpm2aws-aws-lightsail-name')) {
            wp_die('Error! Instance Name Can Not be Determined<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }
        if (false === get_option('wpm2aws-iam-user') || '' === get_option('wpm2aws-iam-user')) {
            wp_die('Error! IAM User Name Can Not be Determined<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }

        // Get SnapShot Name
        if (empty($snapshotDetails['name'])) {
            wp_die(print_r($snapshotDetails));
        }
        $snapshotName = $snapshotDetails['name'];

        // Get Region Zone
        if (null !== $launchRegion) {
            $regions = $this->getLightsailRegions($lightsailClient);
            $availableRegions = $this->lightsailRegionAvailability($regions, $launchRegion);
            $launchAvailabilityZoneId = $this->getAvailabilityZone($availableRegions, $launchRegion);
        } else {
            $regions = $this->getLightsailRegions($lightsailClient);
            $availableRegions = $this->lightsailRegionAvailability($regions);
            $launchAvailabilityZoneId = $this->getAvailabilityZone($availableRegions);
        }


        // Get Bundle (or fail)
        if (null !== $launchPlanId) {
            if (empty($launchPlanId)) {
                wp_die(print_r($snapshotDetails));
            }
            $launchInstanceBundleId = $launchPlanId;
        } else {
            if (empty($snapshotDetails['fromBundleId'])) {
                wp_die(print_r($snapshotDetails));
            }
            $launchInstanceBundleId = $snapshotDetails['fromBundleId'];
        }


        $userTag = get_option('wpm2aws-iam-user');

        if (
            false !==  get_option('wpm2aws_valid_licence_type') &&
            'TRIAL' === strtoupper(get_option('wpm2aws_valid_licence_type'))
        ) {
            $userRef = str_replace('@', '-', get_option('wpm2aws_licence_email'));
            $userRef = str_replace('.', '-', $userRef);
            
            $userTag .= '-' . $userRef;
        }

        $launchInstanceName = get_option('wpm2aws-aws-lightsail-name') . '_' . $snapshotName;

        // Get User Key Pair
        $keyPairName = $this->getKeyPairName($lightsailClient);

        $lightsailParams = array(
            'addOns' => [
                [
                    'addOnType' => 'AutoSnapshot', // REQUIRED
                    'autoSnapshotAddOnRequest' => [
                        'snapshotTimeOfDay' => '02:00' // '<string>',
                    ],
                ],
            ],
            'availabilityZone' => $launchAvailabilityZoneId, //'<string>', // REQUIRED
            'bundleId' => $launchInstanceBundleId, // '<string>', // REQUIRED
            'instanceNames' => [$launchInstanceName], // ['<string>', ...], // REQUIRED
            'instanceSnapshotName' => $snapshotName, // '<string>',
            'tags' => array(
                array(
                    'key' => 'create-origin',
                    'value' => 'wpm2aws'
                ),
                array(
                    'key' => 'user-origin',
                    'value' => $userTag,
                )
            )
        );

        if (false !== $keyPairName) {
            $lightsailParams['keyPairName'] = $keyPairName;
        }
        // wp_die(print_r($lightsailParams));
        try {
            $instance = $lightsailClient->createInstancesFromSnapshot($lightsailParams);
        } catch (ApiException $e) {
            $msg = "Error Mgs: createInstancesFromSnapshot<br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage();
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
            throw new Exception($msg);
        } catch (LightsailException $e) {
            $msg = "Error Mgs: createInstancesFromSnapshot<br><strong>" . $e->get() . '</strong>';
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
            throw new Exception($msg);
        } catch (Exception $e) {
            $msg = "Error Mgs: createInstancesFromSnapshot<br><strong>" . $e->getMessage() . '</strong>';
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
            throw new Exception($msg);
        }

        return $instance['operations'];
    }


    public function copySnapshotFromRegion($lightsailClient, $snapshotDetails, $fromRegion)
    {
        // Get SnapShot Name
        if (empty($snapshotDetails['name'])) {
            wp_die(print_r($snapshotDetails));
        }
        $snapshotName = $snapshotDetails['name'];

        try {
            $copy = $lightsailClient->copySnapshot(
                [
                    // 'restoreDate' => '<string>',
                    'sourceRegion' => $fromRegion , // REQUIRED 'us-east-1|us-east-2|us-west-1|us-west-2|eu-west-1|eu-west-2|eu-west-3|eu-central-1|ca-central-1|ap-south-1|ap-southeast-1|ap-southeast-2|ap-northeast-1|ap-northeast-2', // REQUIRED
                    // 'sourceResourceName' => '<string>',
                    'sourceSnapshotName' => $snapshotName, // '<string>',
                    'targetSnapshotName' => $snapshotName, // REQUIRED // '<string>',
                    // 'useLatestRestorableAutoSnapshot' => true || false,
                ]
            );
        } catch (ApiException $e) {
            $msg = "Error Mgs (API): copySnapshot<br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage();
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
            throw new Exception($msg);
        } catch (LightsailException $e) {
            $msg = "Error Mgs (LS): copySnapshot<br><strong>" . $e->get() . '</strong>';
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
            throw new Exception($msg);
        } catch (Exception $e) {
            $msg = "Error Mgs (ST): copySnapshot<br><strong>" . $e->getMessage() . '</strong>';
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
            throw new Exception($msg);
        }

        return $copy['operations'];
    }


    /**
     * Change the Region of the Instance
     */
    public function changeInstanceRegion($newRegion, $snapshotName)
    {
        if (false === get_option('wpm2aws-iam-user') || '' === get_option('wpm2aws-iam-user')) {
            wp_die('Error! IAM User Name Can Not be Determined<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }




        // Make a Snapshot
        // $snapshot = false;
        // $snapShotName = 'changeRegion-' . get_option('wpm2aws-iam-user');
        // try {
        //     $snapshot = $this->createManualSnapshot($snapShotName);
        // } catch (Exception $e) {
        //     $msg = "Error Mgs: <br><strong>" . $e->getMessage() . '</strong>';
        //     set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
        //     throw new Exception($msg);
        // }

        // if (!$snapshot) {
        //     return false;
        // }


        // Set up AWS Client
        try {
            $lightsailClient = new LightsailClient($this->credentials);
        } catch (ApiException $e) {
            $msg = "Error Mgs: LightsailClient<br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage();
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
            throw new Exception($msg);
        } catch (Exception $e) {
            $msg = "Error Mgs: LightsailClient<br><strong>" . $e->getMessage() . '</strong>';
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
            throw new Exception($msg);
        }


        // Get Snapshot Details
        $snapshotDetails = false;
        try {
            $snapshotDetails = $this->getSnapshotByName($lightsailClient, $snapshotName);
        } catch (Exception $e) {
            $msg = "Error Mgs: getSnapshotByName<br><strong>" . $e->getMessage() . '</strong>';
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
            throw new Exception($msg);
        }

        if (!$snapshotDetails) {
            return false;
        }


        $homeRegion = $this->credentials['region'];
        $this->credentials['region'] = $newRegion;

        try {
            $lightsailClient = new LightsailClient($this->credentials);
        } catch (ApiException $e) {
            $this->credentials['region'] = $homeRegion;
            $msg = "Error Mgs: LightsailClient<br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage();
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
            throw new Exception($msg);
        } catch (Exception $e) {
            $this->credentials['region'] = $homeRegion;
            $msg = "Error Mgs: LightsailClient<br><strong>" . $e->getMessage() . '</strong>';
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
            throw new Exception($msg);
        }


        try {
            $allRegionSnapshots = $this->getInstanceSnapshots($lightsailClient);
        } catch (Exception $e) {
            $msg = "Error Mgs: getInstanceSnapshots<br><strong>" . $e->getMessage() . '</strong>';
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
            throw new Exception($msg);
        }

        $snapShotExists = false;
        if (!empty($allRegionSnapshots)) {
            foreach ($allRegionSnapshots as $rssIx => $rssVals) {
                if ($snapshotName === $rssVals['name']) {
                    $snapShotExists = true;
                }
            }
        }

        if (!$snapShotExists) {
            try {
                $copiedSnapshot = $this->copySnapshotFromRegion($lightsailClient, $snapshotDetails, $homeRegion);
                // wp_die(print_r($copiedSnapshot));
                return $copiedSnapshot;
            } catch (Exception $e) {
                $this->credentials['region'] = $homeRegion;
                $msg = "Error Mgs: copySnapshotFromRegion<br><strong>" . $e->getMessage() . '</strong>';
                set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
                throw new Exception($msg);
            }
        }

        try {
            $newInstance = $this->createInstanceFromSnapshot($lightsailClient, $snapshotDetails, $newRegion, null);
        } catch (Exception $e) {
            $this->credentials['region'] = $homeRegion;
            $msg = "Error Mgs: createInstanceFromSnapshotNewRegion<br><strong>" . $e->getMessage() . '</strong>';
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
            throw new Exception($msg);
        }

        $this->credentials['region'] = $homeRegion;

        // print_r($newInstance);
        return $newInstance;
    }


    /**
     * Change the Plan of the Instance
     */
    // public function changeInstancePlan($planId, $snapshotName)
    // {
    //     if (false === get_option('wpm2aws-iam-user') || '' === get_option('wpm2aws-iam-user')) {
    //         wp_die('Error! IAM User Name Can Not be Determined<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
    //     }

    //     // Set up AWS Client
    //     try {
    //         $lightsailClient = new LightsailClient($this->credentials);
    //     } catch (ApiException $e) {
    //         $msg = "Error Mgs: LightsailClient<br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage();
    //         set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
    //         throw new Exception($msg);
    //     } catch (Exception $e) {
    //         $msg = "Error Mgs: LightsailClient<br><strong>" . $e->getMessage() . '</strong>';
    //         set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
    //         throw new Exception($msg);
    //     }


    //     // Get Snapshot Details
    //     $snapshotDetails = false;
    //     try {
    //         $snapshotDetails = $this->getSnapshotByName($lightsailClient, $snapshotName);
    //     } catch (Exception $e) {
    //         $msg = "Error Mgs: getSnapshotByName<br><strong>" . $e->getMessage() . '</strong>';
    //         set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
    //         throw new Exception($msg);
    //     }

    //     if (!$snapshotDetails) {
    //         return false;
    //     }


    //     try {
    //         $newInstance = $this->createInstanceFromSnapshot($lightsailClient, $snapshotDetails, null, $planId);
    //     } catch (Exception $e) {
    //         $msg = "Error Mgs: createInstanceFromSnapshotNewPlan<br><strong>" . $e->getMessage() . '</strong>';
    //         set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
    //         throw new Exception($msg);
    //     }

    //     try {
    //         $attachedIp = $this->getAttachedStaticIp($lightsailClient);
    //     } catch (Exception $e) {
    //         $msg = "Error Mgs: getAttachedStaticIp<br><strong>" . $e->getMessage() . '</strong>';
    //         set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
    //         throw new Exception($msg);
    //     }


    //     if (!empty($attachedIp['ipAddress']) && !empty($attachedIp['name'])) {
    //         try {
    //             $detatchIp = $this->detachStaticIp($lightsailClient, $attachedIp['name']);
    //         } catch (Exception $e) {
    //             $msg = "Error Mgs: detachStaticIp<br><strong>" . $e->getMessage() . '</strong>';
    //             set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
    //             throw new Exception($msg);
    //         }

    //         try {
    //             $attatchIp = $this->attachStaticIp($lightsailClient, $newInstance[0]['resourceName'], $attachedIp['name']);
    //         } catch (Exception $e) {
    //             $msg = "Error Mgs: attachStaticIp<br><strong>" . $e->getMessage() . '</strong>';
    //             set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
    //             throw new Exception($msg);
    //         }
    //     }

    //     return $newInstance;
    // }


    /**
     * Change the Plan of the Instance - Via API
     */
    public function remoteChangeInstancePlan($planId, $snapShotName)
    {
        // if (false === get_option('wpm2aws-lightsail-instance-details')) {
        //     return array('error' => 'Invalid Request Parameters (1)', 'code' => '401');
        // }

        if (false === get_option('wpm2aws-iampw')) {
            return array('error' => 'Invalid Request Parameters (2)', 'code' => '401');
        }

        if (false === get_option('wpm2aws-iamid')) {
            return array('error' => 'Invalid Request Parameters (3)', 'code' => '401');
        }

        if (empty($_SERVER['SERVER_ADDR'])) {
            return array('error' => 'Invalid Request Parameters (4)', 'code' => '401');
        }
        
        if (empty($planId)) {
            return array('error' => 'Invalid Request Parameters (5)', 'code' => '401');
        }

        if (empty($snapShotName)) {
            return array('error' => 'Invalid Request Parameters (5)', 'code' => '401');
        }
        
        $key = get_option('wpm2aws-iamid');
        $secret = get_option('wpm2aws-iampw');

        if (
            false !==  get_option('wpm2aws_valid_licence_type') &&
            'TRIAL' === strtoupper(get_option('wpm2aws_valid_licence_type'))
        ) {
            $key = str_replace(get_option('wpm2aws_valid_licence_dyck'), '', base64_decode(get_option('wpm2aws-iamid')));
            $secret = str_replace(get_option('wpm2aws_valid_licence_dyck'), '', base64_decode(get_option('wpm2aws-iampw')));
        }

        $requestData = array(
            'wpm2aws-key' => $key,
            'wpm2aws-token' => $secret,
            'wpm2aws-user-email' => get_option('wpm2aws_licence_email'),
            // 'wpm2aws-instancearn' => get_option('wpm2aws-lightsail-instance-details')['details']['arn'],
            'wpm2aws-instancename' => get_option('wpm2aws-aws-lightsail-name'),
            'wpm2aws-serveraddress' => (!empty($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : ''),
            'wpm2aws-blundleplanid' => $planId,
            'wpm2aws-snapshotname' => $snapShotName,
        );

        // wp_die(print_r($instanceDetails));
        // $response = wp_remote_post( 'http://localhost/wpm2aws-console-api/public/api/console/changePlan', array(
        $response = wp_remote_post(
            'http://52.213.129.222/api/console/changePlan',
            array(

            'method' => 'POST',
            'timeout' => 45,
            'redirection' => 10,
            'httpversion' => '1.0',
            'blocking' => true,
            'headers' => array(
                'Cache-Control' => 'no-cache',
            ),
            'body' => array( 'data' => $requestData ),
            'cookies' => array(),
            )
        );

        $responseCode = wp_remote_retrieve_response_code($response);
        // wp_die(print_r($response));
        if ('200' !== $responseCode && 200 !== $responseCode) {
            $errorMessage = json_decode($response['body'], true);
            if ((int)$responseCode >= 400 && (int)$responseCode < 500) {
                // wp_die(print_r($errorMessage));
                throw new Exception('Unauthorised Access<br>' . $errorMessage);
                return array('error' => 'Unauthorised Access<br>' . $errorMessage, 'code' => $responseCode);
            } else {
                // wp_die(print_r($errorMessage));
                throw new Exception('Unauthorised Access<br>' . $errorMessage);
                return array('error' => 'An Error Has Occurred<br>' . $errorMessage, 'code' => $responseCode);
            }
        }

        if (!isset($response['body'])) {
            return $data = array();
        }

        // wp_die(print_r($response));
        $remoteData = json_decode($response['body'], true);


        if (!isset($remoteData['remote-data'])) {
            return $data = array();
        }
        
        return $remoteData['remote-data'];


















        // if (false === get_option('wpm2aws-iam-user') || '' === get_option('wpm2aws-iam-user')) {
        //     wp_die('Error! IAM User Name Can Not be Determined<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        // }

        // // Set up AWS Client
        // try {
        //     $lightsailClient = new LightsailClient($this->credentials);
        // } catch (ApiException $e) {
        //     $msg = "Error Mgs: LightsailClient<br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage();
        //     set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
        //     throw new Exception($msg);
        // } catch (Exception $e) {
        //     $msg = "Error Mgs: LightsailClient<br><strong>" . $e->getMessage() . '</strong>';
        //     set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
        //     throw new Exception($msg);
        // }


        // // Get Snapshot Details
        // $snapshotDetails = false;
        // try {
        //     $snapshotDetails = $this->getSnapshotByName($lightsailClient, $snapshotName);
        // } catch (Exception $e) {
        //     $msg = "Error Mgs: getSnapshotByName<br><strong>" . $e->getMessage() . '</strong>';
        //     set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
        //     throw new Exception($msg);
        // }

        // if (!$snapshotDetails) {
        //     return false;
        // }


        // try {
        //     $newInstance = $this->createInstanceFromSnapshot($lightsailClient, $snapshotDetails, null, $planId);
        // } catch (Exception $e) {
        //     $msg = "Error Mgs: createInstanceFromSnapshotNewPlan<br><strong>" . $e->getMessage() . '</strong>';
        //     set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
        //     throw new Exception($msg);
        // }

        // try {
        //     $attachedIp = $this->getAttachedStaticIp($lightsailClient);
        // } catch (Exception $e) {
        //     $msg = "Error Mgs: getAttachedStaticIp<br><strong>" . $e->getMessage() . '</strong>';
        //     set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
        //     throw new Exception($msg);
        // }


        // if (!empty($attachedIp['ipAddress']) && !empty($attachedIp['name'])) {
        //     try {
        //         $detatchIp = $this->detachStaticIp($lightsailClient, $attachedIp['name']);
        //     } catch (Exception $e) {
        //         $msg = "Error Mgs: detachStaticIp<br><strong>" . $e->getMessage() . '</strong>';
        //         set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
        //         throw new Exception($msg);
        //     }

        //     try {
        //         $attatchIp = $this->attachStaticIp($lightsailClient, $newInstance[0]['resourceName'], $attachedIp['name']);
        //     } catch (Exception $e) {
        //         $msg = "Error Mgs: attachStaticIp<br><strong>" . $e->getMessage() . '</strong>';
        //         set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
        //         throw new Exception($msg);
        //     }
        // }

        // return $newInstance;
    }


    private function getAttachedStaticIp($lightsailClient)
    {
        if (false === get_option('wpm2aws-aws-lightsail-name') || '' === get_option('wpm2aws-aws-lightsail-name')) {
            wp_die('Error! Instance Name Can Not be Determined<br><br>Return to <a href="' . admin_url('/admin.php?page=wpm2aws') . '">Plugin Page</a>');
        }

        try {
            $allIps = $this->getAllStaticIps($lightsailClient);
        } catch (ApiException $e) {
            $msg = "Error Mgs: getAttachedStaticIp<br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage();
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
            throw new Exception($msg);
        } catch (LightsailException $e) {
            $msg = "Error Mgs: getAllStaticIps<br><strong>" . $e->get() . '</strong>';
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
            throw new Exception($msg);
        } catch (Exception $e) {
            $msg = "Error Mgs: getAllStaticIps<br><strong>" . $e->getMessage() . '</strong>';
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
            throw new Exception($msg);
        }

        foreach ($allIps as $ipIx => $ipVals) {
            if (!empty($ipVals['attachedTo'])) {
                if (get_option('wpm2aws-aws-lightsail-name') === $ipVals['attachedTo']) {
                    return $ipVals;
                }
            }
        }
    }

    private function detachStaticIp($lightsailClient, $staticIpName)
    {
        try {
            $detachedIp = $lightsailClient->detachStaticIp(
                [
                    'staticIpName' => $staticIpName, // '<string>', // REQUIRED
                ]
            );
        } catch (ApiException $e) {
            $msg = "Error Mgs: detachStaticIp<br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage();
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
            throw new Exception($msg);
        } catch (Exception $e) {
            $msg = "Error Mgs: detachStaticIp<br><strong>" . $e->getMessage() . '</strong>';
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
            throw new Exception($msg);
        }

        return $detachedIp['operations'];
    }

    private function attachStaticIp($lightsailClient, $instanceName, $staticIpName)
    {
        try {
            $attachedIp = $lightsailClient->attachStaticIp([
                'instanceName' => $instanceName, // '<string>', // REQUIRED
                'staticIpName' => $staticIpName, // '<string>', // REQUIRED
            ]);
        } catch (ApiException $e) {
            $msg = "Error Mgs: attachStaticIp<br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage();
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
            throw new Exception($msg);
        } catch (Exception $e) {
            $msg = "Error Mgs: attachStaticIp<br><strong>" . $e->getMessage() . '</strong>';
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
            throw new Exception($msg);
        }

        return $attachedIp['operations'];
    }

    private function getAllStaticIps($lightsailClient)
    {
        // Get All IPs
        try {
            $allIps = $lightsailClient->getStaticIps();
        } catch (ApiException $e) {
            $msg = "Error Mgs: getAllStaticIps<br><strong>" . $e->getAwsErrorCode() . '</strong><br>' . $e->getAwsErrorMessage();
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
            throw new Exception($msg);
        } catch (Exception $e) {
            $msg = "Error Mgs: getAllStaticIps<br><strong>" . $e->getMessage() . '</strong>';
            set_transient('wpm2aws_admin_error_notice_' . get_current_user_id(), $msg);
            throw new Exception($msg);
        }

        return $allIps['staticIps'];
    }
}
