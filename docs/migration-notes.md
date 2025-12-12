Migration notes: vipreseller_status uniqueness

This repository includes a migration that adds unique indexes for provider status records:

- `database/migrations/2025_12_12_000001_add_unique_indexes_for_provider_statuses.php`

Why this matters
- Production systems may already have duplicate `trxid` values in `vipreseller_status`.
- Adding a unique index will fail on MySQL if duplicates exist (error SQLSTATE[23000]: Integrity constraint violation).

What the migration does
- Best-effort deduplication: for any duplicated `trxid`, it will keep one row (prefer one with `order_id`) and nullify the `trxid` on other rows so the unique index can be created.
- The migration wraps index creation and drops in try/catch to be resilient across DB drivers.

Recommended pre-checks (run on production BEFORE migration)

1) Find duplicates:

```sql
SELECT trxid, COUNT(*) AS cnt
FROM vipreseller_status
WHERE trxid IS NOT NULL
GROUP BY trxid
HAVING cnt > 1;
```

2) Inspect duplicates (example export):

```sql
SELECT * FROM vipreseller_status
WHERE trxid IN (
  SELECT trxid FROM (
    SELECT trxid FROM vipreseller_status
    WHERE trxid IS NOT NULL
    GROUP BY trxid
    HAVING COUNT(*) > 1
  ) tmp
);
```

3) Backup data (mysqldump example):

```bash
mysqldump -u user -p --single-transaction --skip-lock-tables database vipreseller_status > vipreseller_status_dump.sql
```

If you prefer not to let the migration automatically modify rows in-place, perform a manual cleanup of duplicates (e.g., move duplicates to an archival table or adjust rows) and then run the migration.

If you want, I can produce a small one-off cleanup script that lists duplicates and lets you choose which row to preserve interactively (or preserves the most recent or the one with order_id).