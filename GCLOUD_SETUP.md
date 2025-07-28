# Google Cloud IAM Setup for GitHub Actions

This document outlines the steps required to set up Workload Identity Federation for GitHub Actions to authenticate with Google Cloud services.

## Prerequisites

- Google Cloud Project with billing enabled
- GitHub repository
- Owner or Editor permissions on the Google Cloud Project
- `gcloud` CLI installed and authenticated

## Step 1: Enable Required APIs

```bash
gcloud services enable iam.googleapis.com
gcloud services enable iamcredentials.googleapis.com
gcloud services enable run.googleapis.com
gcloud services enable bigquery.googleapis.com
gcloud services enable cloudbuild.googleapis.com
```

## Step 2: Create Service Account

```bash
# Create service account
gcloud iam service-accounts create github-actions-sa \
    --description="Service account for GitHub Actions deployments" \
    --display-name="GitHub Actions Service Account"

# Get the service account email
SERVICE_ACCOUNT_EMAIL="github-actions-sa@PROJECT_ID.iam.gserviceaccount.com"
```

## Step 3: Grant Required IAM Roles

```bash
PROJECT_ID="your-project-id"

# Cloud Run Admin (for deployments)
gcloud projects add-iam-policy-binding $PROJECT_ID \
    --member="serviceAccount:$SERVICE_ACCOUNT_EMAIL" \
    --role="roles/run.admin"

# BigQuery User (for data access)
gcloud projects add-iam-policy-binding $PROJECT_ID \
    --member="serviceAccount:$SERVICE_ACCOUNT_EMAIL" \
    --role="roles/bigquery.user"

# Storage Admin (for Cloud Run container images)
gcloud projects add-iam-policy-binding $PROJECT_ID \
    --member="serviceAccount:$SERVICE_ACCOUNT_EMAIL" \
    --role="roles/storage.admin"

# Service Account User (for impersonation)
gcloud projects add-iam-policy-binding $PROJECT_ID \
    --member="serviceAccount:$SERVICE_ACCOUNT_EMAIL" \
    --role="roles/iam.serviceAccountUser"

# Cloud Build Editor (for source deployments)
gcloud projects add-iam-policy-binding $PROJECT_ID \
    --member="serviceAccount:$SERVICE_ACCOUNT_EMAIL" \
    --role="roles/cloudbuild.builds.editor"
```

## Step 4: Create Workload Identity Pool

```bash
POOL_ID="github-actions-pool"

# Create Workload Identity Pool
gcloud iam workload-identity-pools create $POOL_ID \
    --location="global" \
    --description="Pool for GitHub Actions"

# Get the pool's full resource name
gcloud iam workload-identity-pools describe $POOL_ID \
    --location="global" \
    --format="value(name)"
```

## Step 5: Create Workload Identity Provider

```bash
PROVIDER_ID="github-provider"
GITHUB_REPO="owner/repository-name"  # Replace with your GitHub repo

# Create provider
gcloud iam workload-identity-pools providers create-oidc $PROVIDER_ID \
    --location="global" \
    --workload-identity-pool=$POOL_ID \
    --issuer-uri="https://token.actions.githubusercontent.com" \
    --attribute-mapping="google.subject=assertion.sub,attribute.actor=assertion.actor,attribute.repository=assertion.repository" \
    --attribute-condition="assertion.repository=='$GITHUB_REPO'"
```

## Step 6: Allow Provider to Impersonate Service Account

```bash
# Get the provider's full resource name
PROVIDER_RESOURCE_NAME="projects/PROJECT_NUMBER/locations/global/workloadIdentityPools/$POOL_ID/providers/$PROVIDER_ID"

# Allow the provider to impersonate the service account
gcloud iam service-accounts add-iam-policy-binding $SERVICE_ACCOUNT_EMAIL \
    --role="roles/iam.workloadIdentityUser" \
    --member="principalSet://iam.googleapis.com/$PROVIDER_RESOURCE_NAME/attribute.repository/$GITHUB_REPO"
```

## Step 7: Set GitHub Repository Secrets

Add the following secrets to your GitHub repository (Settings > Secrets and variables > Actions):

| Secret Name | Value | Description |
|-------------|-------|-------------|
| `WIF_PROVIDER` | `projects/PROJECT_NUMBER/locations/global/workloadIdentityPools/POOL_ID/providers/PROVIDER_ID` | Full provider resource name |
| `WIF_SERVICE_ACCOUNT` | `github-actions-sa@PROJECT_ID.iam.gserviceaccount.com` | Service account email |
| `GCP_PROJECT_ID` | `your-project-id` | Google Cloud Project ID |
| `BIGQUERY_PROJECT_ID` | `your-project-id` | BigQuery Project ID (can be different) |
| `CLOUD_RUN_SERVICE` | `rise-take-home` | Cloud Run service name |

## Step 8: Get Required Values

```bash
# Get PROJECT_NUMBER (needed for WIF_PROVIDER)
gcloud projects describe PROJECT_ID --format="value(projectNumber)"

# Get full provider resource name
echo "projects/PROJECT_NUMBER/locations/global/workloadIdentityPools/$POOL_ID/providers/$PROVIDER_ID"

# Verify setup
gcloud iam workload-identity-pools providers describe $PROVIDER_ID \
    --location="global" \
    --workload-identity-pool=$POOL_ID
```

## Testing the Setup

1. Push code to trigger the GitHub Actions workflow
2. Check that authentication succeeds in the workflow logs
3. Verify deployment to Cloud Run
4. Test BigQuery connectivity via the health check endpoint

## Transfer Process

When transferring between organizations:

1. **Source Organization**: Export all secret values
2. **Target Organization**: 
   - Run all setup steps with new project ID
   - Update GitHub secrets with new values
   - Update any hardcoded project references in code
3. **Validation**: Run deployment workflow to ensure everything works

## Troubleshooting

- **Authentication fails**: Check that PROJECT_NUMBER is correct (not PROJECT_ID)
- **Permission denied**: Verify all IAM roles are assigned correctly
- **Provider not found**: Wait 5 minutes for IAM changes to propagate
- **BigQuery access denied**: Ensure BigQuery API is enabled and service account has `bigquery.user` role

## Security Notes

- Workload Identity Federation provides short-lived tokens (1 hour)
- No long-lived service account keys are stored in GitHub secrets
- Access is restricted to the specific GitHub repository
- All authentication is audited in Google Cloud Console