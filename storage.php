<?php
interface IFileIO
{
  function save($data);
  function load();
}

abstract class FileIO implements IFileIO
{
  protected $filepath;

  public function __construct($filename)
  {
    if (!is_readable($filename) || !is_writable($filename)) {
      throw new Exception("Data source $filename is invalid.");
    }
    $this->filepath = realpath($filename);
  }
}

class JsonIO extends FileIO
{
  public function load($assoc = true)
  {
    $file_content = file_get_contents($this->filepath);
    return json_decode($file_content, $assoc) ?: [];
  }

  public function save($data)
  {
    $json_content = json_encode($data, JSON_PRETTY_PRINT);
    file_put_contents($this->filepath, $json_content);
  }
}

class SerializeIO extends FileIO
{
  public function load()
  {
    $file_content = file_get_contents($this->filepath);
    return unserialize($file_content) ?: [];
  }

  public function save($data)
  {
    $serialized_content = serialize($data);
    file_put_contents($this->filepath, $serialized_content);
  }
}

interface IStorage
{
  function add($record): string;
  function findById(string $id);
  function findAll(array $params = []);
  function findOne(array $params = []);
  function update(string $id, $record);
  function delete(string $id);

  function findMany(callable $condition);
  function updateMany(callable $condition, callable $updater);
  function deleteMany(callable $condition);
}

class Storage implements IStorage
{
  protected $contents;
  protected $io;

  public function __construct(IFileIO $io, $assoc = true)
  {
    $this->io = $io;
    $this->contents = (array)$this->io->load($assoc);
  }

  public function __destruct()
  {
    $this->io->save($this->contents);
  }

  public function add($record): string
  {
    $id = uniqid(); // Generate a unique ID
    $record['id'] = $id; // Ensure the car's ID field matches the key
    $this->contents[$id] = $record; // Store the car using the same ID as the key
    return $id;
  }

  public function findById($id)
  {
    foreach ($this->contents as $item) {
      if ((int)$item['id'] === (int)$id) { // Match by 'id' field in the car object
        return $item;
      }
    }
    return null; // Return null if no match found
  }


  public function findAll(array $params = [])
  {
    return array_filter($this->contents, function ($item) use ($params) {
      foreach ($params as $key => $value) {
        if (((array)$item)[$key] !== $value) {
          return FALSE;
        }
      }
      return TRUE;
    });
  }

  public function findOne(array $params = [])
  {
    $found_items = $this->findAll($params);
    $first_index = array_keys($found_items)[0] ?? NULL;
    return $found_items[$first_index] ?? NULL;
  }

  public function update(string $id, $record)
  {
    if (isset($this->contents[$id])) {
      $record['id'] = $id; // Ensure the car's ID field matches the key
      $this->contents[$id] = $record;
    }
  }

  public function delete(string $id)
  {
    unset($this->contents[$id]);
  }

  public function findMany(callable $condition)
  {
    return array_filter($this->contents, $condition);
  }

  public function updateMany(callable $condition, callable $updater)
  {
    array_walk($this->contents, function (&$item) use ($condition, $updater) {
      if ($condition($item)) {
        $updater($item);
      }
    });
  }

  public function save()
  {
    // Use the correct save method to save to file (JSON, Serialize, etc.)
    $this->io->save($this->contents);
  }

  public function deleteMany(callable $condition)
  {
    $this->contents = array_filter($this->contents, function ($item) use ($condition) {
      return !$condition($item);
    });
  }
}
