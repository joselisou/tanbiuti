export interface WxrTerm {
  taxonomy: string;
  name: string;
}

export interface WxrPost {
  /** Registered WordPress post type slug, e.g. "tanbiuti_comanda". */
  postType: string;
  title: string;
  /** "YYYY-MM-DD HH:mm:ss" in site local time. */
  postDate: string;
  status?: string;
  /** Meta values are stringified; WordPress postmeta has no native typing. */
  meta?: Record<string, string | number | null>;
  terms?: WxrTerm[];
}

export interface WxrOptions {
  siteTitle: string;
  siteUrl: string;
  authorLogin: string;
}

function slugify(title: string): string {
  return title
    .normalize('NFD')
    .replace(/[̀-ͯ]/g, '')
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/(^-|-$)/g, '')
    .slice(0, 200);
}

/** Wraps text in a CDATA section, splitting any literal "]]>" so it can't break out early. */
function cdata(text: string): string {
  return `<![CDATA[${text.replace(/]]>/g, ']]]]><![CDATA[>')}]]>`;
}

/** Escapes text for use in a plain (non-CDATA) XML text node or attribute value. */
function xmlEscape(text: string): string {
  return text
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

function renderItem(post: WxrPost, postId: number, options: WxrOptions): string {
  const slug = `${slugify(post.postType)}-${postId}-${slugify(post.title)}`;
  const permalink = `${options.siteUrl}/?post_type=${post.postType}&p=${postId}`;

  const categories = (post.terms ?? [])
    .map(
      (term) =>
        `    <category domain="${xmlEscape(term.taxonomy)}" nicename="${xmlEscape(slugify(term.name))}">${cdata(
          term.name,
        )}</category>`,
    )
    .join('\n');

  const postmeta = Object.entries(post.meta ?? {})
    .map(
      ([key, value]) => `    <wp:postmeta>
      <wp:meta_key>${cdata(key)}</wp:meta_key>
      <wp:meta_value>${cdata(value === null || value === undefined ? '' : String(value))}</wp:meta_value>
    </wp:postmeta>`,
    )
    .join('\n');

  return `  <item>
    <title>${cdata(post.title)}</title>
    <link>${xmlEscape(permalink)}</link>
    <pubDate>${new Date(post.postDate.replace(' ', 'T') + 'Z').toUTCString()}</pubDate>
    <dc:creator>${cdata(options.authorLogin)}</dc:creator>
    <guid isPermaLink="false">${xmlEscape(permalink)}</guid>
    <description></description>
    <content:encoded>${cdata('')}</content:encoded>
    <excerpt:encoded>${cdata('')}</excerpt:encoded>
    <wp:post_id>${postId}</wp:post_id>
    <wp:post_date>${post.postDate}</wp:post_date>
    <wp:post_date_gmt>${post.postDate}</wp:post_date_gmt>
    <wp:comment_status>closed</wp:comment_status>
    <wp:ping_status>closed</wp:ping_status>
    <wp:post_name>${cdata(slug)}</wp:post_name>
    <wp:status>${xmlEscape(post.status ?? 'publish')}</wp:status>
    <wp:post_parent>0</wp:post_parent>
    <wp:menu_order>0</wp:menu_order>
    <wp:post_type>${xmlEscape(post.postType)}</wp:post_type>
    <wp:post_password></wp:post_password>
    <wp:is_sticky>0</wp:is_sticky>
${categories ? categories + '\n' : ''}${postmeta}
  </item>`;
}

// Deliberately not `new Date()`: buildWxr's output must be byte-for-byte reproducible from the
// same input (fake-data-generator's dataset and the test fixture are both committed to git and
// checked in CI for drift), so nothing in it may depend on wall-clock time.
const CHANNEL_PUB_DATE = 'Thu, 01 Jan 1970 00:00:00 GMT';

/** Builds a WXR 1.2 XML document from a flat list of posts. Post IDs are assigned sequentially. */
export function buildWxr(posts: WxrPost[], options: WxrOptions): string {
  const items = posts.map((post, index) => renderItem(post, index + 1, options)).join('\n');

  return `<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0"
  xmlns:excerpt="http://wordpress.org/export/1.2/excerpt/"
  xmlns:content="http://purl.org/rss/1.0/modules/content/"
  xmlns:wfw="http://wellformedweb.org/CommentAPI/"
  xmlns:dc="http://purl.org/dc/elements/1.1/"
  xmlns:wp="http://wordpress.org/export/1.2/"
>
<channel>
  <title>${cdata(options.siteTitle)}</title>
  <link>${xmlEscape(options.siteUrl)}</link>
  <description>${cdata('Tanbiuti data export')}</description>
  <pubDate>${CHANNEL_PUB_DATE}</pubDate>
  <language>pt-BR</language>
  <wp:wxr_version>1.2</wp:wxr_version>
  <wp:base_site_url>${xmlEscape(options.siteUrl)}</wp:base_site_url>
  <wp:base_blog_url>${xmlEscape(options.siteUrl)}</wp:base_blog_url>
  <wp:author>
    <wp:author_id>1</wp:author_id>
    <wp:author_login>${cdata(options.authorLogin)}</wp:author_login>
    <wp:author_email>${cdata('')}</wp:author_email>
  </wp:author>
${items}
</channel>
</rss>
`;
}
