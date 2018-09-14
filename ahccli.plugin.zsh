# ------------------------------------------------------------------------------
#          FILE:  ahccli.plugin.zsh
#   DESCRIPTION:  oh-my-zsh ahccli plugin file.
#        AUTHOR:  Jitendra Adhikari (jiten.adhikary@gmail.com)
#       VERSION:  0.0.1
#       LICENSE:  MIT
# ------------------------------------------------------------------------------
# Specifically tuned to support autocompletion for apps build with adhocore/cli!
#         Check https://github.com/adhocore/php-cli#autocompletion
# ------------------------------------------------------------------------------

# AhcCli command completion
_ahccli_command_list () {
  command $1 --help 2>/dev/null | sed "1,/Commands/d" | gawk 'match($0, /  ([a-z]+) [a-z]*  /, c) { print c[1] }'
}

# AhcCli option completion
_ahccli_option_list () {
  command $1 $2 --help 2>/dev/null | sed '1,/Options/d' | gawk 'match($0, /  .*(--[a-z-]+)(\.\.\.)?.    /, o) { print o[1] }'
}

# AhcCli compdef handler
_ahccli () {
  local curcontext="$curcontext" state line cmd subcmd
  typeset -A opt_args
  _arguments '1: :->cmd' '*: :->opts'

  cmd=`echo $curcontext | gawk 'match($0, /\:([_a-z-]+)\:$/, c) { print c[1] }'`
  subcmd=`echo $line | awk '{print $1}'`

  case $state in
    cmd) compadd $(_ahccli_command_list $cmd) ;;
    opts) compadd $(_ahccli_option_list $cmd $subcmd) ;;
  esac
}

#
# Register commands for autocompletion below:
#
# format:  compdef _ahccli <cmd>
# example: compdef _ahccli phint
#
