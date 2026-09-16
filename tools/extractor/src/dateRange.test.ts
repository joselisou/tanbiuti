import { describe, expect, it } from 'vitest';
import { eachDate } from './dateRange.js';

describe('eachDate', () => {
  it('returns a single date when start === end', () => {
    expect(eachDate('2025-07-19', '2025-07-19')).toEqual(['2025-07-19']);
  });

  it('returns an inclusive range spanning a month boundary', () => {
    expect(eachDate('2025-07-30', '2025-08-02')).toEqual([
      '2025-07-30',
      '2025-07-31',
      '2025-08-01',
      '2025-08-02',
    ]);
  });

  it('handles a leap-day range', () => {
    expect(eachDate('2028-02-27', '2028-03-01')).toEqual([
      '2028-02-27',
      '2028-02-28',
      '2028-02-29',
      '2028-03-01',
    ]);
  });

  it('throws on an invalid date', () => {
    expect(() => eachDate('not-a-date', '2025-07-19')).toThrow();
  });
});
