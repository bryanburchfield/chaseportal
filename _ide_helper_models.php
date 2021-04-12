<?php

// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * App\Models\ActiveNumber
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ActiveNumber newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ActiveNumber newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ActiveNumber query()
 */
	class ActiveNumber extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\AdvancedTable
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\AdvancedTableField[] $advancedTableFields
 * @property-read int|null $advanced_table_fields_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Campaign[] $campaigns
 * @property-read int|null $campaigns_count
 * @method static \Illuminate\Database\Eloquent\Builder|AdvancedTable newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdvancedTable newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdvancedTable query()
 */
	class AdvancedTable extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\AdvancedTableField
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Campaign[] $campaigns
 * @property-read int|null $campaigns_count
 * @property-read \App\Models\FieldType $fieldType
 * @method static \Illuminate\Database\Eloquent\Builder|AdvancedTableField newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdvancedTableField newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdvancedTableField query()
 */
	class AdvancedTableField extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Api
 *
 * @property int $id
 * @property string $name
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ApiTransaction[] $api_transactions
 * @property-read int|null $api_transactions_count
 * @method static \Illuminate\Database\Eloquent\Builder|Api newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Api newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Api query()
 * @method static \Illuminate\Database\Eloquent\Builder|Api whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Api whereName($value)
 */
	class Api extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ApiTransaction
 *
 * @property int $id
 * @property int $api_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Api $api
 * @method static \Illuminate\Database\Eloquent\Builder|ApiTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ApiTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ApiTransaction query()
 * @method static \Illuminate\Database\Eloquent\Builder|ApiTransaction whereApiId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApiTransaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApiTransaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApiTransaction whereUpdatedAt($value)
 */
	class ApiTransaction extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\AreaCode
 *
 * @property int $npa
 * @property string $city
 * @property string|null $state
 * @property string|null $timezone
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\NearbyAreaCode[] $nearbyAreaCodes
 * @property-read int|null $nearby_area_codes_count
 * @method static \Illuminate\Database\Eloquent\Builder|AreaCode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AreaCode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AreaCode query()
 * @method static \Illuminate\Database\Eloquent\Builder|AreaCode whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AreaCode whereNpa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AreaCode whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AreaCode whereTimezone($value)
 */
	class AreaCode extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\AutomatedReport
 *
 * @property int $id
 * @property int $user_id
 * @property string $report
 * @property string|null $filters
 * @method static \Illuminate\Database\Eloquent\Builder|AutomatedReport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AutomatedReport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AutomatedReport query()
 * @method static \Illuminate\Database\Eloquent\Builder|AutomatedReport whereFilters($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AutomatedReport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AutomatedReport whereReport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AutomatedReport whereUserId($value)
 */
	class AutomatedReport extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Broadcast
 *
 * @property int $id
 * @property string $channel
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Broadcast newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Broadcast newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Broadcast query()
 * @method static \Illuminate\Database\Eloquent\Builder|Broadcast whereChannel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Broadcast whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Broadcast whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Broadcast whereUpdatedAt($value)
 */
	class Broadcast extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Campaign
 *
 * @property-read \App\Models\AdvancedTable $advancedTable
 * @method static \Illuminate\Database\Eloquent\Builder|Campaign newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Campaign newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Campaign query()
 */
	class Campaign extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ContactsPlaybook
 *
 * @property int $id
 * @property int $group_id
 * @property string $name
 * @property string $campaign
 * @property string|null $last_run_from
 * @property string|null $last_run_to
 * @property int $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PlaybookCampaign[] $playbook_campaigns
 * @property-read int|null $playbook_campaigns_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PlaybookRun[] $playbook_runs
 * @property-read int|null $playbook_runs_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PlaybookSubcampaign[] $playbook_subcampaigns
 * @property-read int|null $playbook_subcampaigns_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PlaybookTouch[] $playbook_touches
 * @property-read int|null $playbook_touches_count
 * @method static \Illuminate\Database\Eloquent\Builder|ContactsPlaybook newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ContactsPlaybook newQuery()
 * @method static \Illuminate\Database\Query\Builder|ContactsPlaybook onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|ContactsPlaybook query()
 * @method static \Illuminate\Database\Eloquent\Builder|ContactsPlaybook whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactsPlaybook whereCampaign($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactsPlaybook whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactsPlaybook whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactsPlaybook whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactsPlaybook whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactsPlaybook whereLastRunFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactsPlaybook whereLastRunTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactsPlaybook whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ContactsPlaybook whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|ContactsPlaybook withTrashed()
 * @method static \Illuminate\Database\Query\Builder|ContactsPlaybook withoutTrashed()
 */
	class ContactsPlaybook extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\DailyPhoneFlag
 *
 * @property-write mixed $swap_error
 * @method static \Illuminate\Database\Eloquent\Builder|DailyPhoneFlag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DailyPhoneFlag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DailyPhoneFlag query()
 */
	class DailyPhoneFlag extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Dialer
 *
 * @property int $id
 * @property int $dialer_numb
 * @property string $dialer_name
 * @property string $dialer_fqdn
 * @property string $reporting_db
 * @property string|null $status_url
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|Dialer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Dialer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Dialer query()
 * @method static \Illuminate\Database\Eloquent\Builder|Dialer whereDialerFqdn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dialer whereDialerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dialer whereDialerNumb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dialer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dialer whereReportingDb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Dialer whereStatusUrl($value)
 */
	class Dialer extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\DialingResult
 *
 * @method static \Illuminate\Database\Eloquent\Builder|DialingResult newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DialingResult newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DialingResult query()
 */
	class DialingResult extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Dispo
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Dispo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Dispo newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Dispo query()
 */
	class Dispo extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\DncFile
 *
 * @property int $id
 * @property int $group_id
 * @property int $user_id
 * @property string $filename
 * @property string|null $description
 * @property string $uploaded_at
 * @property string|null $process_started_at
 * @property string|null $processed_at
 * @property string|null $reverse_started_at
 * @property string|null $reversed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $action
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\DncFileDetail[] $dncFileDetails
 * @property-read int|null $dnc_file_details_count
 * @method static \Illuminate\Database\Eloquent\Builder|DncFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DncFile newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DncFile query()
 * @method static \Illuminate\Database\Eloquent\Builder|DncFile whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DncFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DncFile whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DncFile whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DncFile whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DncFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DncFile whereProcessStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DncFile whereProcessedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DncFile whereReverseStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DncFile whereReversedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DncFile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DncFile whereUploadedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DncFile whereUserId($value)
 */
	class DncFile extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\DncFileDetail
 *
 * @property int $id
 * @property int $dnc_file_id
 * @property int $line
 * @property string $phone
 * @property string|null $processed_at
 * @property int|null $succeeded
 * @property string|null $error
 * @property-read \App\Models\DncFile $dncFile
 * @method static \Illuminate\Database\Eloquent\Builder|DncFileDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DncFileDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DncFileDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder|DncFileDetail whereDncFileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DncFileDetail whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DncFileDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DncFileDetail whereLine($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DncFileDetail wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DncFileDetail whereProcessedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|DncFileDetail whereSucceeded($value)
 */
	class DncFileDetail extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\EmailServiceProvider
 *
 * @property int $id
 * @property int $group_id
 * @property int $user_id
 * @property string $name
 * @property string $provider_type
 * @property array|null $properties
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|EmailServiceProvider newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailServiceProvider newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailServiceProvider query()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailServiceProvider whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailServiceProvider whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailServiceProvider whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailServiceProvider whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailServiceProvider whereProperties($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailServiceProvider whereProviderType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailServiceProvider whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EmailServiceProvider whereUserId($value)
 */
	class EmailServiceProvider extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\FeatureMessage
 *
 * @property int $id
 * @property string $title
 * @property string $body
 * @property int|null $active
 * @property string $created_at
 * @property string|null $expires_at
 * @property-read mixed $text_body
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ReadFeatureMessage[] $readFeatureMessages
 * @property-read int|null $read_feature_messages_count
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureMessage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureMessage query()
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureMessage whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureMessage whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureMessage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureMessage whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureMessage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureMessage whereTitle($value)
 */
	class FeatureMessage extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\FieldType
 *
 * @method static \Illuminate\Database\Eloquent\Builder|FieldType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FieldType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FieldType query()
 */
	class FieldType extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Group
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Group newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Group newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Group query()
 */
	class Group extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\InboundSource
 *
 * @method static \Illuminate\Database\Eloquent\Builder|InboundSource newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InboundSource newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InboundSource query()
 */
	class InboundSource extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\InternalPhoneCount
 *
 * @property int $id
 * @property string $run_date
 * @property int $did_count
 * @property string|null $completed_at
 * @method static \Illuminate\Database\Eloquent\Builder|InternalPhoneCount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InternalPhoneCount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InternalPhoneCount query()
 * @method static \Illuminate\Database\Eloquent\Builder|InternalPhoneCount whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InternalPhoneCount whereDidCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InternalPhoneCount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InternalPhoneCount whereRunDate($value)
 */
	class InternalPhoneCount extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\InternalPhoneFlag
 *
 * @property int $id
 * @property string $run_date
 * @property string $period
 * @property int $group_id
 * @property string $group_name
 * @property int $dialer_numb
 * @property string $phone
 * @property string|null $ring_group
 * @property string|null $subcampaigns
 * @property int|null $dials
 * @property int|null $connects
 * @property string|null $connect_pct
 * @property string|null $replaced_by
 * @property string|null $swap_error
 * @method static \Illuminate\Database\Eloquent\Builder|InternalPhoneFlag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InternalPhoneFlag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InternalPhoneFlag query()
 * @method static \Illuminate\Database\Eloquent\Builder|InternalPhoneFlag whereConnectPct($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InternalPhoneFlag whereConnects($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InternalPhoneFlag whereDialerNumb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InternalPhoneFlag whereDials($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InternalPhoneFlag whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InternalPhoneFlag whereGroupName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InternalPhoneFlag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InternalPhoneFlag wherePeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InternalPhoneFlag wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InternalPhoneFlag whereReplacedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InternalPhoneFlag whereRingGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InternalPhoneFlag whereRunDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InternalPhoneFlag whereSubcampaigns($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InternalPhoneFlag whereSwapError($value)
 */
	class InternalPhoneFlag extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Kpi
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $outer_sql
 * @property string $inner_sql
 * @property int $union_all
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\KpiGroup[] $kpiGroups
 * @property-read int|null $kpi_groups_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\KpiRecipient[] $kpiRecipients
 * @property-read int|null $kpi_recipients_count
 * @method static \Illuminate\Database\Eloquent\Builder|Kpi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kpi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Kpi query()
 * @method static \Illuminate\Database\Eloquent\Builder|Kpi whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kpi whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kpi whereInnerSql($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kpi whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kpi whereOuterSql($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kpi whereUnionAll($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Kpi whereUpdatedAt($value)
 */
	class Kpi extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\KpiGroup
 *
 * @property int $id
 * @property int $kpi_id
 * @property int $group_id
 * @property int $active
 * @property int|null $interval
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\Kpi $kpi
 * @method static \Illuminate\Database\Eloquent\Builder|KpiGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KpiGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KpiGroup query()
 * @method static \Illuminate\Database\Eloquent\Builder|KpiGroup whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiGroup whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiGroup whereInterval($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiGroup whereKpiId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiGroup whereUpdatedAt($value)
 */
	class KpiGroup extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * App\Models\KpiRecipient
 *
 * @property int $id
 * @property int $kpi_id
 * @property int $recipient_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\Kpi $kpi
 * @property-read \App\Models\Recipient $recipient
 * @method static \Illuminate\Database\Eloquent\Builder|KpiRecipient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KpiRecipient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|KpiRecipient query()
 * @method static \Illuminate\Database\Eloquent\Builder|KpiRecipient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiRecipient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiRecipient whereKpiId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiRecipient whereRecipientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|KpiRecipient whereUpdatedAt($value)
 */
	class KpiRecipient extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * App\Models\Lead
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Lead newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Lead newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Lead query()
 */
	class Lead extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\NearbyAreaCode
 *
 * @property int $source_npa
 * @property int $nearby_npa
 * @property-read \App\Models\AreaCode $nearbyAreaCode
 * @property-read \App\Models\AreaCode $sourceAreaCode
 * @method static \Illuminate\Database\Eloquent\Builder|NearbyAreaCode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NearbyAreaCode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NearbyAreaCode query()
 * @method static \Illuminate\Database\Eloquent\Builder|NearbyAreaCode whereNearbyNpa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|NearbyAreaCode whereSourceNpa($value)
 */
	class NearbyAreaCode extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\OwnedDid
 *
 * @method static \Illuminate\Database\Eloquent\Builder|OwnedDid newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OwnedDid newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OwnedDid query()
 */
	class OwnedDid extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PauseCode
 *
 * @property int $id
 * @property int $group_id
 * @property int $user_id
 * @property string $code
 * @property int $minutes_per_day
 * @property int $times_per_day
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PauseCode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PauseCode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PauseCode query()
 * @method static \Illuminate\Database\Eloquent\Builder|PauseCode whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PauseCode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PauseCode whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PauseCode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PauseCode whereMinutesPerDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PauseCode whereTimesPerDay($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PauseCode whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PauseCode whereUserId($value)
 */
	class PauseCode extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PhoneFlag
 *
 * @property int $id
 * @property string $run_date
 * @property int $group_id
 * @property string $group_name
 * @property int $dialer_numb
 * @property string $phone
 * @property string|null $ring_group
 * @property int $calls
 * @property string $connect_ratio
 * @property int $owned
 * @property int $checked
 * @property string|null $flagged
 * @property string|null $replaced_by
 * @property string|null $flags
 * @property int|null $callerid_check
 * @property string|null $swap_error
 * @property int $connects
 * @method static \Illuminate\Database\Eloquent\Builder|PhoneFlag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PhoneFlag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PhoneFlag query()
 * @method static \Illuminate\Database\Eloquent\Builder|PhoneFlag whereCalleridCheck($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhoneFlag whereCalls($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhoneFlag whereChecked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhoneFlag whereConnectRatio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhoneFlag whereConnects($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhoneFlag whereDialerNumb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhoneFlag whereFlagged($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhoneFlag whereFlags($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhoneFlag whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhoneFlag whereGroupName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhoneFlag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhoneFlag whereOwned($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhoneFlag wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhoneFlag whereReplacedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhoneFlag whereRingGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhoneFlag whereRunDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PhoneFlag whereSwapError($value)
 */
	class PhoneFlag extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PhoneReswap
 *
 * @method static \Illuminate\Database\Eloquent\Builder|PhoneReswap newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PhoneReswap newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PhoneReswap query()
 */
	class PhoneReswap extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PlaybookAction
 *
 * @property int $id
 * @property int $group_id
 * @property string $name
 * @property string|null $campaign
 * @property string $action_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\PlaybookEmailAction|null $playbook_email_action
 * @property-read \App\Models\PlaybookLeadAction|null $playbook_lead_action
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PlaybookRunTouchAction[] $playbook_run_touch_actions
 * @property-read int|null $playbook_run_touch_actions_count
 * @property-read \App\Models\PlaybookSmsAction|null $playbook_sms_action
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PlaybookTouchAction[] $playbook_touch_actions
 * @property-read int|null $playbook_touch_actions_count
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookAction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookAction newQuery()
 * @method static \Illuminate\Database\Query\Builder|PlaybookAction onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookAction query()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookAction whereActionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookAction whereCampaign($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookAction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookAction whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookAction whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookAction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookAction whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookAction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|PlaybookAction withTrashed()
 * @method static \Illuminate\Database\Query\Builder|PlaybookAction withoutTrashed()
 */
	class PlaybookAction extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PlaybookCampaign
 *
 * @property int $id
 * @property int $contacts_playbook_id
 * @property string $campaign
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookCampaign newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookCampaign newQuery()
 * @method static \Illuminate\Database\Query\Builder|PlaybookCampaign onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookCampaign query()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookCampaign whereCampaign($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookCampaign whereContactsPlaybookId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookCampaign whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookCampaign whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookCampaign whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookCampaign whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|PlaybookCampaign withTrashed()
 * @method static \Illuminate\Database\Query\Builder|PlaybookCampaign withoutTrashed()
 */
	class PlaybookCampaign extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PlaybookEmailAction
 *
 * @property int $id
 * @property int $playbook_action_id
 * @property int $email_service_provider_id
 * @property string $email_field
 * @property string $from
 * @property string $subject
 * @property int $template_id
 * @property int $emails_per_lead
 * @property int|null $days_between_emails
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookEmailAction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookEmailAction newQuery()
 * @method static \Illuminate\Database\Query\Builder|PlaybookEmailAction onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookEmailAction query()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookEmailAction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookEmailAction whereDaysBetweenEmails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookEmailAction whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookEmailAction whereEmailField($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookEmailAction whereEmailServiceProviderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookEmailAction whereEmailsPerLead($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookEmailAction whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookEmailAction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookEmailAction wherePlaybookActionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookEmailAction whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookEmailAction whereTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookEmailAction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|PlaybookEmailAction withTrashed()
 * @method static \Illuminate\Database\Query\Builder|PlaybookEmailAction withoutTrashed()
 */
	class PlaybookEmailAction extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PlaybookFilter
 *
 * @property int $id
 * @property int $group_id
 * @property string $name
 * @property string|null $campaign
 * @property string $field
 * @property string $operator
 * @property string|null $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $operator_name
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PlaybookTouchFilter[] $playbook_touch_filters
 * @property-read int|null $playbook_touch_filters_count
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookFilter newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookFilter newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookFilter query()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookFilter whereCampaign($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookFilter whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookFilter whereField($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookFilter whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookFilter whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookFilter whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookFilter whereOperator($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookFilter whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookFilter whereValue($value)
 */
	class PlaybookFilter extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PlaybookLeadAction
 *
 * @property int $id
 * @property int $playbook_action_id
 * @property string|null $to_campaign
 * @property string|null $to_subcampaign
 * @property string|null $to_callstatus
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookLeadAction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookLeadAction newQuery()
 * @method static \Illuminate\Database\Query\Builder|PlaybookLeadAction onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookLeadAction query()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookLeadAction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookLeadAction whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookLeadAction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookLeadAction wherePlaybookActionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookLeadAction whereToCallstatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookLeadAction whereToCampaign($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookLeadAction whereToSubcampaign($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookLeadAction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|PlaybookLeadAction withTrashed()
 * @method static \Illuminate\Database\Query\Builder|PlaybookLeadAction withoutTrashed()
 */
	class PlaybookLeadAction extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PlaybookOptout
 *
 * @property int $id
 * @property int $group_id
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookOptout newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookOptout newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookOptout query()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookOptout whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookOptout whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookOptout whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookOptout whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookOptout whereUpdatedAt($value)
 */
	class PlaybookOptout extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PlaybookRun
 *
 * @property int $id
 * @property int $contacts_playbook_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ContactsPlaybook $contacts_playbook
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PlaybookRunTouch[] $playbook_run_touches
 * @property-read int|null $playbook_run_touches_count
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRun newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRun newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRun query()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRun whereContactsPlaybookId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRun whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRun whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRun whereUpdatedAt($value)
 */
	class PlaybookRun extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PlaybookRunTouch
 *
 * @property int $id
 * @property int $playbook_run_id
 * @property int $playbook_touch_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PlaybookRun $playbook_run
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PlaybookRunTouchAction[] $playbook_run_touch_actions
 * @property-read int|null $playbook_run_touch_actions_count
 * @property-read \App\Models\PlaybookTouch $playbook_touch
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRunTouch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRunTouch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRunTouch query()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRunTouch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRunTouch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRunTouch wherePlaybookRunId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRunTouch wherePlaybookTouchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRunTouch whereUpdatedAt($value)
 */
	class PlaybookRunTouch extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PlaybookRunTouchAction
 *
 * @property int $id
 * @property int $playbook_run_touch_id
 * @property int $playbook_action_id
 * @property string|null $reversed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $process_started_at
 * @property string|null $processed_at
 * @property string|null $reverse_started_at
 * @property-read \App\Models\PlaybookAction $playbook_action
 * @property-read \App\Models\PlaybookRunTouch $playbook_run_touch
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PlaybookRunTouchActionDetail[] $playbook_run_touch_action_details
 * @property-read int|null $playbook_run_touch_action_details_count
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRunTouchAction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRunTouchAction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRunTouchAction query()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRunTouchAction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRunTouchAction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRunTouchAction wherePlaybookActionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRunTouchAction wherePlaybookRunTouchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRunTouchAction whereProcessStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRunTouchAction whereProcessedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRunTouchAction whereReverseStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRunTouchAction whereReversedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRunTouchAction whereUpdatedAt($value)
 */
	class PlaybookRunTouchAction extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PlaybookRunTouchActionDetail
 *
 * @property int $id
 * @property int $playbook_run_touch_action_id
 * @property string $reporting_db
 * @property int $lead_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $old_campaign
 * @property string|null $old_subcampaign
 * @property string|null $old_callstatus
 * @property string|null $old_email
 * @property string|null $old_phone
 * @property-read \App\Models\PlaybookRunTouchAction $playbook_run_touch_action
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRunTouchActionDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRunTouchActionDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRunTouchActionDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRunTouchActionDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRunTouchActionDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRunTouchActionDetail whereLeadId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRunTouchActionDetail whereOldCallstatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRunTouchActionDetail whereOldCampaign($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRunTouchActionDetail whereOldEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRunTouchActionDetail whereOldPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRunTouchActionDetail whereOldSubcampaign($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRunTouchActionDetail wherePlaybookRunTouchActionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRunTouchActionDetail whereReportingDb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookRunTouchActionDetail whereUpdatedAt($value)
 */
	class PlaybookRunTouchActionDetail extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PlaybookSmsAction
 *
 * @property int $id
 * @property int $playbook_action_id
 * @property int $sms_from_number_id
 * @property int $template_id
 * @property int $sms_per_lead
 * @property int|null $days_between_sms
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\SmsFromNumber $sms_from_number
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookSmsAction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookSmsAction newQuery()
 * @method static \Illuminate\Database\Query\Builder|PlaybookSmsAction onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookSmsAction query()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookSmsAction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookSmsAction whereDaysBetweenSms($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookSmsAction whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookSmsAction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookSmsAction wherePlaybookActionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookSmsAction whereSmsFromNumberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookSmsAction whereSmsPerLead($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookSmsAction whereTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookSmsAction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|PlaybookSmsAction withTrashed()
 * @method static \Illuminate\Database\Query\Builder|PlaybookSmsAction withoutTrashed()
 */
	class PlaybookSmsAction extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PlaybookSubcampaign
 *
 * @property int $id
 * @property int $contacts_playbook_id
 * @property string $subcampaign
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookSubcampaign newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookSubcampaign newQuery()
 * @method static \Illuminate\Database\Query\Builder|PlaybookSubcampaign onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookSubcampaign query()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookSubcampaign whereContactsPlaybookId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookSubcampaign whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookSubcampaign whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookSubcampaign whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookSubcampaign whereSubcampaign($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookSubcampaign whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|PlaybookSubcampaign withTrashed()
 * @method static \Illuminate\Database\Query\Builder|PlaybookSubcampaign withoutTrashed()
 */
	class PlaybookSubcampaign extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PlaybookTouch
 *
 * @property int $id
 * @property int $contacts_playbook_id
 * @property string $name
 * @property int $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\ContactsPlaybook $contacts_playbook
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PlaybookTouchAction[] $playbook_touch_actions
 * @property-read int|null $playbook_touch_actions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PlaybookTouchFilter[] $playbook_touch_filters
 * @property-read int|null $playbook_touch_filters_count
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookTouch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookTouch newQuery()
 * @method static \Illuminate\Database\Query\Builder|PlaybookTouch onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookTouch query()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookTouch whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookTouch whereContactsPlaybookId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookTouch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookTouch whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookTouch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookTouch whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookTouch whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|PlaybookTouch withTrashed()
 * @method static \Illuminate\Database\Query\Builder|PlaybookTouch withoutTrashed()
 */
	class PlaybookTouch extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PlaybookTouchAction
 *
 * @property int $id
 * @property int $playbook_touch_id
 * @property int $playbook_action_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\PlaybookAction $playbook_action
 * @property-read \App\Models\PlaybookTouch $playbook_touch
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookTouchAction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookTouchAction newQuery()
 * @method static \Illuminate\Database\Query\Builder|PlaybookTouchAction onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookTouchAction query()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookTouchAction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookTouchAction whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookTouchAction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookTouchAction wherePlaybookActionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookTouchAction wherePlaybookTouchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookTouchAction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|PlaybookTouchAction withTrashed()
 * @method static \Illuminate\Database\Query\Builder|PlaybookTouchAction withoutTrashed()
 */
	class PlaybookTouchAction extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PlaybookTouchFilter
 *
 * @property int $id
 * @property int $playbook_touch_id
 * @property int $playbook_filter_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PlaybookFilter $playbook_filter
 * @property-read \App\Models\PlaybookTouch $playbook_touch
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookTouchFilter newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookTouchFilter newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookTouchFilter query()
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookTouchFilter whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookTouchFilter whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookTouchFilter wherePlaybookFilterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookTouchFilter wherePlaybookTouchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlaybookTouchFilter whereUpdatedAt($value)
 */
	class PlaybookTouchFilter extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ReadFeatureMessage
 *
 * @property int $id
 * @property int $feature_message_id
 * @property int $user_id
 * @property string $read_at
 * @property-read \App\Models\FeatureMessage $featureMessage
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|ReadFeatureMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReadFeatureMessage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReadFeatureMessage query()
 * @method static \Illuminate\Database\Eloquent\Builder|ReadFeatureMessage whereFeatureMessageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReadFeatureMessage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReadFeatureMessage whereReadAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReadFeatureMessage whereUserId($value)
 */
	class ReadFeatureMessage extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Recipient
 *
 * @property int $id
 * @property string $name
 * @property string|null $email
 * @property string|null $phone
 * @property int $group_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $user_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\KpiRecipient[] $kpiRecipients
 * @property-read int|null $kpi_recipients_count
 * @method static \Illuminate\Database\Eloquent\Builder|Recipient newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Recipient newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Recipient query()
 * @method static \Illuminate\Database\Eloquent\Builder|Recipient whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Recipient whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Recipient whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Recipient whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Recipient whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Recipient wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Recipient whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Recipient whereUserId($value)
 */
	class Recipient extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * App\Models\Rep
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Rep newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Rep newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Rep query()
 */
	class Rep extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\ReportPermission
 *
 * @property int $id
 * @property int $user_id
 * @property string $report_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|ReportPermission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReportPermission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ReportPermission query()
 * @method static \Illuminate\Database\Eloquent\Builder|ReportPermission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportPermission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportPermission whereReportName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportPermission whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ReportPermission whereUserId($value)
 */
	class ReportPermission extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Script
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Script newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Script newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Script query()
 */
	class Script extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\SmsFromNumber
 *
 * @property int $id
 * @property int $group_id
 * @property string $from_number
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PlaybookSmsAction[] $playbook_sms_actions
 * @property-read int|null $playbook_sms_actions_count
 * @method static \Illuminate\Database\Eloquent\Builder|SmsFromNumber newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SmsFromNumber newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SmsFromNumber query()
 * @method static \Illuminate\Database\Eloquent\Builder|SmsFromNumber whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsFromNumber whereFromNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsFromNumber whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsFromNumber whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SmsFromNumber whereUpdatedAt($value)
 */
	class SmsFromNumber extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\SpamCheckBatch
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $description
 * @property string $uploaded_at
 * @property string|null $process_started_at
 * @property string|null $processed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SpamCheckBatchDetail[] $spamCheckBatchDetails
 * @property-read int|null $spam_check_batch_details_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|SpamCheckBatch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SpamCheckBatch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SpamCheckBatch query()
 * @method static \Illuminate\Database\Eloquent\Builder|SpamCheckBatch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpamCheckBatch whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpamCheckBatch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpamCheckBatch whereProcessStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpamCheckBatch whereProcessedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpamCheckBatch whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpamCheckBatch whereUploadedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpamCheckBatch whereUserId($value)
 */
	class SpamCheckBatch extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\SpamCheckBatchDetail
 *
 * @property int $id
 * @property int $spam_check_batch_id
 * @property int $line
 * @property string $phone
 * @property int|null $succeeded
 * @property string|null $error
 * @property int $checked
 * @property int|null $flagged
 * @property string|null $flags
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SpamCheckBatch $spamcCheckBatch
 * @method static \Illuminate\Database\Eloquent\Builder|SpamCheckBatchDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SpamCheckBatchDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SpamCheckBatchDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder|SpamCheckBatchDetail whereChecked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpamCheckBatchDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpamCheckBatchDetail whereError($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpamCheckBatchDetail whereFlagged($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpamCheckBatchDetail whereFlags($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpamCheckBatchDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpamCheckBatchDetail whereLine($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpamCheckBatchDetail wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpamCheckBatchDetail whereSpamCheckBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpamCheckBatchDetail whereSucceeded($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpamCheckBatchDetail whereUpdatedAt($value)
 */
	class SpamCheckBatchDetail extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\SqlSrvModel
 *
 * @method static \Illuminate\Database\Eloquent\Builder|SqlSrvModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SqlSrvModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SqlSrvModel query()
 */
	class SqlSrvModel extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\System
 *
 * @method static \Illuminate\Database\Eloquent\Builder|System newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|System newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|System query()
 */
	class System extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string $user_type
 * @property int $group_id
 * @property string $db
 * @property string $tz
 * @property string|null $app_token
 * @property string|null $additional_dbs
 * @property string|null $persist_filters
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $language
 * @property int|null $language_displayed
 * @property string|null $phone
 * @property string|null $expiration
 * @property string $theme
 * @property int $active
 * @property int|null $dialer_id
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\Dialer|null $dialer
 * @property-read mixed $expires_in
 * @property-read mixed $iana_tz
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ReadFeatureMessage[] $readFeatureMessages
 * @property-read int|null $read_feature_messages_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ReportPermission[] $reportPermissions
 * @property-read int|null $report_permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SpamCheckBatch[] $spamCheckBatches
 * @property-read int|null $spam_check_batches_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\UserAudit[] $userAudits
 * @property-read int|null $user_audits_count
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAdditionalDbs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAppToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDialerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereExpiration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLanguageDisplayed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePersistFilters($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTheme($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTz($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUserType($value)
 */
	class User extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * App\Models\UserAudit
 *
 * @property int $id
 * @property string $event_at
 * @property string|null $ip
 * @property int|null $user_id
 * @property string|null $email
 * @property string $action
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|UserAudit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserAudit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserAudit query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserAudit whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAudit whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAudit whereEventAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAudit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAudit whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserAudit whereUserId($value)
 */
	class UserAudit extends \Eloquent {}
}

