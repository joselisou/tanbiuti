/** Returns ISO date strings (YYYY-MM-DD) from `start` to `end`, inclusive, in ascending order. */
export function eachDate(start: string, end: string): string[] {
  const dates: string[] = [];
  const cursor = new Date(`${start}T00:00:00Z`);
  const last = new Date(`${end}T00:00:00Z`);

  if (Number.isNaN(cursor.getTime()) || Number.isNaN(last.getTime())) {
    throw new Error(`Invalid date range: ${start} .. ${end}`);
  }

  while (cursor.getTime() <= last.getTime()) {
    dates.push(cursor.toISOString().slice(0, 10));
    cursor.setUTCDate(cursor.getUTCDate() + 1);
  }

  return dates;
}

/** Today's date as YYYY-MM-DD, in UTC. */
export function todayIso(): string {
  return new Date().toISOString().slice(0, 10);
}
