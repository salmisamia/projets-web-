<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
Simple News Library 0.0.1 https://launchpad.net/simplenewscodeigniter
A CodeIgniter 2.X library for show a simple news/blog in your website
Author: costales http://launchpad.net/~costales 
Based on http://ellislab.com/codeigniter/user-guide/tutorial/news_section.html
Licensed under LGPL3
*/

class Simplenews {

    private $CI;
    private $url_path = '';
    // For database
    private $table       = 'news';
    private $title_field = 'title';
    private $url_field   = 'url';
    private $body_field  = 'body';
    private $tags_field  = 'tags';
    // For template
    private $open            = '';
    private $close           = '';
    private $title_start     = '';
    private $title_end       = '';
    private $body_start      = '';
    private $body_end        = '';
    private $all_tags_start  = '';
    private $tag_start       = '';
    private $tag_end         = '';
    private $all_tags_end    = '';
    private $set_title_links = '';
    
    public function __construct() {
        $this->CI =& get_instance();
    }
    
    /* Create a news
     * @access public
     * @param  string
     * @param  string
     * @param  string
     * @return bool
     */
    function add($title = '', $body = '', $tags = '') {
        // Check data
        if ($title == '' || $body == '')
            return FALSE;
        
        // Title already exists
        $query = $this->CI->db->get_where($this->table, array($this->url_field=>url_title($title)));
        if ($query->num_rows() > 0)
            return FALSE;
        
        // Create news into database
        $data = array($this->title_field=>$title, $this->url_field=>url_title($title), $this->body_field=>$body, $this->tags_field=>$tags);
        if (!$this->CI->db->insert($this->table, $data))
            return FALSE;

        return TRUE;
    }
    
    /* Delete a news
     *
     * @access public
     * @param  string
     * @return bool
     */
    function delete($url_title = '') {
        if ($url_title == '')
            return FALSE;
        
        $data = array($this->url_field=>url_title($url_title));
        if ($this->CI->db->delete($this->table, $data))
            return TRUE; // Deleted
        else
            return FALSE; // Not deleted
    }

    /* Get number of news in database
     *
     * @access public
     * @return integer
     */
    function get_total_news() {
        $query = $this->CI->db->get($this->table);
        return $query->num_rows();
    }

    /* Generate HTML News with offset or tag
     *
     * @access public
     * @param  array
     * @return string
     */
    public function generate($param) {
        // Compose data
        $offset    = (array_key_exists('offset',    $param) ? $param['offset']    : 0);
        $limit     = (array_key_exists('limit',     $param) ? $param['limit']     : 0);
        $tag       = (array_key_exists('tag',       $param) ? $param['tag']       : '');
        $show_tags = (array_key_exists('show_tags', $param) ? $param['show_tags'] : FALSE);

        // Check data
        if ($limit == 0 || ($offset == 0 && $tag == ''))
            return FALSE;

        // Get news by offset
        if ($offset != 0) {
            $query = $this->CI->db->get($this->table, $limit, $offset);
        }
        // Get news by tag
        else {
            $this->CI->db->like($this->tags_field, $tag);
            $query = $this->CI->db->get($this->table, $limit, 0);
        }
        
        // Generate result
        if ($query->num_rows() > 0) {
            echo $this->open;
            foreach ($query->result() as $row) {
                echo ($this->set_title_links == TRUE ? '<a href="'.$this->url_path.url_title($row->title).'">' : '');
                echo $this->title_start.$row->title.$this->title_end;
                echo ($this->set_title_links == TRUE ? '</a>' : '');
                echo $this->body_start.$row->body.$this->body_end;
                if ($show_tags) {
                    echo $this->all_tags_start;
                    $explode_tags = explode(",", $row->tags);
                    foreach ($explode_tags as $current_tag) {
                        echo $this->tag_start.$current_tag.$this->tag_end;
                    }
                    echo $this->all_tags_end;
                }
            }
            echo $this->close;
        }
    }
    
    /* Generate HTML News by title (from a URL link)
     *
     * @access public
     * @param  string
     * @return string
     */
    public function generate_from_title($url_title = '', $show_tags = FALSE) {
        if ($url_title == '')
            return FALSE;
        
        // Generate result
        $query = $this->CI->db->get_where($this->table, array($this->url_field=>url_title($url_title)));
        if ($query->num_rows() == 1) {
            echo $this->open;
            foreach ($query->result() as $row) {
                echo $this->title_start.$row->title.$this->title_end;
                echo $this->body_start.$row->body.$this->body_end;
                if ($show_tags) {
                    echo $this->all_tags_start;
                    $explode_tags = explode(",", $row->tags);
                    foreach ($explode_tags as $current_tag) {
                        echo $this->tag_start.$current_tag.$this->tag_end;
                    }
                    echo $this->all_tags_end;
                }
            }
            echo $this->close;
        }
        else { // For save CSS
            echo $this->open;
            echo $this->close;
        }
    }
    
    /* 
     * Set template for generate News
     * @access public
     * @param  array
     */
    public function set_template($template) {
        // Set template
        $this->open  = (array_key_exists('open',  $template) ? $template['open']  : '');
        $this->close = (array_key_exists('close', $template) ? $template['close'] : '');
        
        $this->title_start = (array_key_exists('title_start', $template) ? $template['title_start'] : '');
        $this->title_end   = (array_key_exists('title_end',   $template) ? $template['title_end']   : '');

        $this->body_start = (array_key_exists('body_start', $template) ? $template['body_start'] : '');
        $this->body_end   = (array_key_exists('body_end',   $template) ? $template['body_end']   : '');

        $this->all_tags_start = (array_key_exists('all_tags_start', $template) ? $template['all_tags_start'] : '');
        $this->tag_start      = (array_key_exists('tag_start',      $template) ? $template['tag_start']      : '');
        $this->tag_end        = (array_key_exists('tag_end',        $template) ? $template['tag_end']        : '');
        $this->all_tags_end   = (array_key_exists('all_tags_end',   $template) ? $template['all_tags_end']   : '');

        $this->set_title_links = (array_key_exists('set_title_links', $template) ? $template['set_title_links'] : FALSE);
    }
    
    /* 
     * Clear the template
     * @access public
     */
    public function clear_template() {
        $this->open            = '';
        $this->close           = '';
        $this->title_start     = '';
        $this->title_end       = '';
        $this->body_start      = '';
        $this->body_end        = '';
        $this->all_tags_start  = '';
        $this->tag_start       = '';
        $this->tag_end         = '';
        $this->all_tags_end    = '';
        $this->set_title_links = '';
    }
    
    /* 
     * Set URL path for title links
     * @access public
     * @param  string
     */
    public function set_url_path($path) {
        $this->url_path = $path;
    }
}
?>
