-- Run in Cursor: select all → right-click → "Run on active connection"
-- Or: Cmd+Shift+P → "SQLTools: Run Selected Query"

SELECT
  id,
  fullname,
  roll,
  department,
  batch,
  email,
  phone,
  weekly_hours,
  meetings,
  created_at
FROM join_applications
ORDER BY created_at DESC;
