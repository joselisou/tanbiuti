import { XMLParser } from 'fast-xml-parser';
import { describe, expect, it } from 'vitest';
import { buildWxr, type WxrPost } from './wxrBuilder.js';

const options = { siteTitle: 'Tanbiuti', siteUrl: 'http://localhost:8888', authorLogin: 'admin' };

function parse(xml: string) {
  const parser = new XMLParser({ ignoreAttributes: false, parseTagValue: false });
  return parser.parse(xml);
}

describe('buildWxr', () => {
  it('produces well-formed XML with one item per post, in order', () => {
    const posts: WxrPost[] = [
      { postType: 'tanbiuti_cliente', title: 'Cliente Um', postDate: '2025-07-19 10:00:00' },
      { postType: 'tanbiuti_comanda', title: 'Comanda #1', postDate: '2025-07-19 10:30:00' },
    ];

    const xml = buildWxr(posts, options);
    const parsed = parse(xml);
    const items = parsed.rss.channel.item;

    expect(Array.isArray(items)).toBe(true);
    expect(items).toHaveLength(2);
    expect(items[0]['wp:post_type']).toBe('tanbiuti_cliente');
    expect(items[1]['wp:post_type']).toBe('tanbiuti_comanda');
  });

  it('round-trips postmeta values, including special characters', () => {
    const posts: WxrPost[] = [
      {
        postType: 'tanbiuti_comanda',
        title: 'Comanda & Cliente <teste>',
        postDate: '2025-07-19 10:00:00',
        meta: { _tanbiuti_source_id: 12345, _tanbiuti_total: 199.9, _tanbiuti_obs: 'Tom & Jerry <special>' },
      },
    ];

    const xml = buildWxr(posts, options);
    const parsed = parse(xml);
    const item = parsed.rss.channel.item;
    const metaEntries: Array<{ 'wp:meta_key': string; 'wp:meta_value': string }> = Array.isArray(
      item['wp:postmeta'],
    )
      ? item['wp:postmeta']
      : [item['wp:postmeta']];

    const asRecord = Object.fromEntries(metaEntries.map((m) => [m['wp:meta_key'], m['wp:meta_value']]));
    expect(asRecord._tanbiuti_source_id).toBe('12345');
    expect(asRecord._tanbiuti_total).toBe('199.9');
    expect(asRecord._tanbiuti_obs).toBe('Tom & Jerry <special>');
    expect(item.title).toBe('Comanda & Cliente <teste>');
  });

  it('emits one category element per taxonomy term', () => {
    const posts: WxrPost[] = [
      {
        postType: 'tanbiuti_comanda',
        title: 'Comanda paga',
        postDate: '2025-07-19 10:00:00',
        terms: [{ taxonomy: 'tanbiuti_comanda_status', name: 'Paga' }],
      },
    ];

    const xml = buildWxr(posts, options);
    const parsed = parse(xml);
    const category = parsed.rss.channel.item.category;

    expect(category['@_domain']).toBe('tanbiuti_comanda_status');
    expect(category['#text']).toBe('Paga');
  });

  it('assigns sequential, stable post ids starting at 1', () => {
    const posts: WxrPost[] = [
      { postType: 'tanbiuti_cliente', title: 'A', postDate: '2025-07-19 10:00:00' },
      { postType: 'tanbiuti_cliente', title: 'B', postDate: '2025-07-19 10:00:00' },
      { postType: 'tanbiuti_cliente', title: 'C', postDate: '2025-07-19 10:00:00' },
    ];

    const xml = buildWxr(posts, options);
    const parsed = parse(xml);
    const ids = parsed.rss.channel.item.map((item: Record<string, unknown>) => item['wp:post_id']);

    expect(ids).toEqual(['1', '2', '3']);
  });
});
