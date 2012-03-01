<?php
/**
 * Field name mapping.
 * This file will be used to map the fields from the imported XML file into database fields for WordPress.
 *
 *  @todo move these options to the plugin options DB table.
 */
$fieldname_mapping = array(
                        'email' => 'user_email',
                        'name' => 'user_nicename',
                        'pass' => 'user_pass',
                        'network'=>'network',
                        'field_company_value' => 'company',
                        'field_country_value' => 'country',
                        'field_job_title_value' => 'job_title',
                        'field_profile_forename_value' => 'first_name',
                        'field_profile_surname_value' => 'last_name',
                        'field_telephone_value' => 'telephone',
                    );