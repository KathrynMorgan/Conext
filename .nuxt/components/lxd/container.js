
module.exports = {
  empty: function () {
    return {
      architecture: '',
      config: {
        'image.architecture': '',
        'image.description': '',
        'image.os': '',
        'image.release': '',
        'image.serial': '',
        'volatile.base_image': '',
        'volatile.eth0.hwaddr': '',
        'volatile.eth0.name': '',
        'volatile.idmap.base': '',
        'volatile.idmap.next': '',
        'volatile.last_state.idmap': '',
        'volatile.last_state.power': '',
        'boot.autostart': false,
        'security.privileged': false,
        'security.nesting': false,
        'limits.cpu': 1,
        'limits.cpu.priority': 5,
        'limits.processes': 500,
        'limits.memory.swap.priority': 5,
        'limits.disk.priority': 5,
        'limits.network.priority': 5,
        'limits.cpu.allowance': 50,
        'limits.memory': 1024,
        'limits.memory.swap': '0',
        'limits.memory.enforce': 'soft'
      },
      devices: {},
      ephemeral: false,
      privileged: false,
      profiles: [
        'default'
      ],
      stateful: true,
      description: '',
      created_at: '',
      expanded_config: {},
      expanded_devices: {},
      name: '',
      status: '',
      status_code: 0,
      last_used_at: '',
      state: {
        status: '',
        status_code: 0,
        disk: {},
        memory: {
          usage: 0,
          usage_peak: 0,
          swap_usage: 0,
          swap_usage_peak: 0
        },
        network: {},
        pid: 0,
        processes: 0,
        cpu: {
          usage: 0
        }
      },
      snapshots: []
    }
  },

  /**
   * in fix: should be called before applying to model
   * - turns string values into numbers
   * - removes % from cpu.allowance
   * - removes MB from limits.memory
   */
  infix: function (container) {
    // get default
    let _default = this.empty()
    // get config
    let config = _default.config
    //
    config['boot.autostart'] = container.config['boot.autostart']
    config['security.privileged'] = container.config['security.privileged']
    config['security.nesting'] = container.config['security.nesting']
    
    // cpu
    config['limits.cpu'] = (container.config['limits.cpu'] ? Number(container.config['limits.cpu']) : _default.config['limits.cpu'])
    config['limits.cpu.priority'] = (container.config['limits.cpu.priority'] ? Number(container.config['limits.cpu.priority']) : _default.config['limits.cpu.priority'])
    config['limits.cpu.allowance'] = (container.config['limits.cpu.allowance'] ? Number(container.config['limits.cpu.allowance'].substring(0, container.config['limits.cpu.allowance'].indexOf('%'))) : _default.config['limits.cpu.allowance'])
    config['limits.processes'] = (container.config['limits.processes'] ? Number(container.config['limits.processes']) : _default.config['limits.processes'])
    
    // disk and network
    config['limits.disk.priority'] = (container.config['limits.disk.priority'] ? Number(container.config['limits.disk.priority']) : _default.config['limits.disk.priority'])
    config['limits.network.priority'] = (container.config['limits.network.priority'] ? Number(container.config['limits.network.priority']) : _default.config['limits.network.priority'])

    // memory
    config['limits.memory'] = (container.config['limits.memory'] ? Number(container.config['limits.memory'].substring(0, container.config['limits.memory'].indexOf('MB'))) : _default.config['limits.memory'])
    config['limits.memory.swap'] = (container.config['limits.memory.swap'] ? container.config['limits.memory.swap'] : 0)
    config['limits.memory.enforce'] = (container.config['limits.memory.enforce'] === 'hard' ? 'hard' : 'soft')
    config['limits.memory.swap.priority'] = (container.config['limits.memory.swap.priority'] ? Number(container.config['limits.memory.swap.priority']) : _default.config['limits.memory.swap.priority'])
    
    // general props - image
    config['image.architecture'] = container.config['image.architecture'] || ''
    config['image.description'] = container.config['image.description'] || ''
    config['image.label'] = container.config['image.label'] || ''
    config['image.os'] = container.config['image.os'] || ''
    config['image.release'] = container.config['image.release'] || ''
    config['image.serial'] = container.config['image.serial'] || ''
    config['image.version'] = container.config['image.version'] || ''
    // general props - volatile
    config['volatile.base_image'] = container.config['volatile.base_image'] || ''
    config['volatile.eth0.hwaddr'] = container.config['volatile.eth0.hwaddr'] || ''
    config['volatile.eth0.name'] = container.config['volatile.eth0.name'] || ''
    config['volatile.idmap.base'] = container.config['volatile.idmap.base'] || ''
    config['volatile.idmap.next'] = container.config['volatile.idmap.next'] || ''
    config['volatile.last_state.idmap'] = container.config['volatile.last_state.idmap'] || ''
    config['volatile.last_state.power'] = container.config['volatile.last_state.power'] || ''

    container.config = config

    return container
  },
  /**
   * out fix: should be called before saving
   * - turns all values into strings
   * - prepends % to cpu.allowance
   * - prepends MB to limits.memory
   */
  outfix: function (container) {
    //
    let config = {
      // autostart
      'boot.autostart': container.config['boot.autostart'],
      // security
      'security.privileged': container.config['security.privileged'],
      'security.nesting': container.config['security.nesting'],
      // cpu
      'limits.cpu': String(container.config['limits.cpu']),
      'limits.cpu.allowance': String(container.config['limits.cpu.allowance']) + '%',
      'limits.cpu.priority': String(container.config['limits.cpu.priority']),
      'limits.processes': String(container.config['limits.processes']),
      // memory
      'limits.memory': String(container.config['limits.memory']) + 'MB',
      'limits.memory.swap': String(container.config['limits.memory.swap']),
      'limits.memory.swap.priority': String(container.config['limits.memory.swap.priority']),
      'limits.memory.enforce': String(container.config['limits.memory.enforce']),
      // disk and network priority
      'limits.disk.priority': String(container.config['limits.disk.priority']),
      'limits.network.priority': String(container.config['limits.network.priority'])
    }

    // fix multis, so can be called from devices
    config['limits.cpu.allowance'] = config['limits.cpu.allowance'].replace('%%', '%')
    config['limits.memory'] = config['limits.memory'].replace('MBMB', 'MB')

    // overwrite config
    container.config = config

    // if !defined set to soft
    if (typeof container.config['limits.memory.enforce'] === 'undefined' || container.config['limits.memory.enforce'] === null) {
      container.config['limits.memory.enforce'] = 'soft'
    }

    // if !defined set to 0
    if (typeof container.config['limits.memory.swap'] === 'undefined' || container.config['limits.memory.swap'] === null) {
      container.config['limits.memory.swap'] = '0'
    }


    return container
  }
}